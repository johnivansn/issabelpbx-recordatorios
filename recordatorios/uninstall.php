<?php
global $db;

function recordatorios_uninstallar_dialplan() {
    $archivo = '/etc/asterisk/extensions_custom.conf';
    if (!is_file($archivo)) {
        return;
    }

    $inicio = '; BEGIN recordatorios';
    $fin = '; END recordatorios';
    $contenido = file_get_contents($archivo);
    $pattern = sprintf('/\n?%s.*?%s\n?/s', preg_quote($inicio, '/'), preg_quote($fin, '/'));
    $contenido = preg_replace($pattern, "\n", $contenido);
    file_put_contents($archivo, rtrim($contenido) . "\n");
}

function recordatorios_uninstall() {
    global $db;

    $db->query("DROP TABLE IF EXISTS recordatorios");

    @unlink('/etc/cron.d/recordatorios');
    @unlink('/var/lib/asterisk/agi-bin/recordatorios.agi.php');
    @unlink('/var/lib/asterisk/agi-bin/recordatorios_estado.agi.php');

    recordatorios_uninstallar_dialplan();

    $menuDir = recordatorios_menu_sonidos_dir();
    if (is_dir($menuDir)) {
        foreach (glob($menuDir . '/*') as $file) {
            if (is_file($file) || is_link($file)) {
                @unlink($file);
            }
        }
        @rmdir($menuDir);
        @rmdir(dirname($menuDir));
    }

    $baseDir = '/var/lib/asterisk/sounds/recordatorios';
    if (is_dir($baseDir)) {
        foreach (glob($baseDir . '/*.wav') as $file) {
            @unlink($file);
        }
        @rmdir($baseDir);
    }

    $logDir = '/var/log/recordatorios';
    if (is_dir($logDir)) {
        foreach (glob($logDir . '/*') as $file) {
            @unlink($file);
        }
        @rmdir($logDir);
    }
}

recordatorios_uninstall();
