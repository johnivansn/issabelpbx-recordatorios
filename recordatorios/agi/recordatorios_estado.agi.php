#!/usr/bin/env php
<?php

if (!defined('ISSABELPBX_IS_AUTH')) {
    define('ISSABELPBX_IS_AUTH', true);
}

$candidatos = [
    '/var/www/html/admin/modules/recordatorios/functions.inc.php',
    __DIR__ . '/../functions.inc.php',
];

foreach ($candidatos as $archivoFunciones) {
    if (is_file($archivoFunciones)) {
        require_once $archivoFunciones;
        break;
    }
}

$estado = $argv[1] ?? '';
$id = isset($argv[2]) ? (int) $argv[2] : 0;

if ($estado === 'ejecutado' && $id > 0) {
    recordatorios_marcar_ejecutado($id);
}
