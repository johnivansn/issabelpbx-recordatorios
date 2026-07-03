#!/usr/bin/env php
<?php

if (!defined('ISSABELPBX_IS_AUTH')) {
    define('ISSABELPBX_IS_AUTH', true);
}

require_once '/var/www/html/admin/modules/recordatorios/functions.inc.php';

$lockFile = fopen('/tmp/recordatorios_cron.lock', 'c');
if (!$lockFile || !flock($lockFile, LOCK_EX | LOCK_NB)) {
    exit;
}

$db = recordatorios_db();
$now = date('Y-m-d H:i:s');

$db->beginTransaction();
try {
    recordatorios_recuperar_procesando_estancado($db);
    $db->commit();
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
}

$stmt = $db->prepare("
    SELECT *
    FROM recordatorios
    WHERE estado = 'pendiente'
      AND alertar_en <= ?
    ORDER BY alertar_en ASC
");
$stmt->execute([$now]);
$recordatorios = $stmt->fetchAll();

foreach ($recordatorios as $r) {
    $id = (int)$r['id'];
    $destino = trim($r['destino_llamada']);
    $audio = pathinfo($r['archivo_audio'], PATHINFO_FILENAME);

    if (!recordatorios_marcar_procesando($id)) {
        continue;
    }

    $callFile = "/var/spool/asterisk/outgoing/recordatorio-$id.call";
    $tmpFile  = $callFile . '.tmp';
    @unlink($callFile);

    $contenido = [];
    $contenido[] = "Channel: Local/s@recordatorios-call";
    $contenido[] = "MaxRetries: 0";
    $contenido[] = "RetryTime: 60";
    $contenido[] = "WaitTime: 30";
    $contenido[] = "Extension: s";
    $contenido[] = "Context: recordatorios-call";
    $contenido[] = "Priority: 1";
    $contenido[] = "Setvar: DESTINO={$destino}";
    $contenido[] = "Setvar: AUDIO={$audio}";
    $contenido[] = "Setvar: ID={$id}";
    $contenido[] = "";

    file_put_contents($tmpFile, implode("\n", $contenido));
    if (rename($tmpFile, $callFile)) {
        recordatorios_marcar_alertado($id);
    } else {
        @unlink($tmpFile);
        recordatorios_marcar_estado($id, 'pendiente');
    }
}
