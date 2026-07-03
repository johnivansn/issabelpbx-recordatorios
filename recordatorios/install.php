<?php
global $db, $amp_conf;

if (!defined('ISSABELPBX_IS_AUTH')) {
    define('ISSABELPBX_IS_AUTH', true);
}

require_once __DIR__ . '/functions.inc.php';

function recordatorios_instalar_archivo($origen, $destino) {
    $dir = dirname($destino);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (file_exists($destino) || is_link($destino)) {
        @unlink($destino);
    }

    if (!@symlink($origen, $destino)) {
        copy($origen, $destino);
    }

    @chmod($destino, 0755);
    @chmod($origen, 0755);
}

function recordatorios_actualizar_dialplan($agregar = true) {
    $archivo = '/etc/asterisk/extensions_custom.conf';
    $inicio = '; BEGIN recordatorios';
    $fin = '; END recordatorios';
    $bloque = <<<CONF
$inicio
[from-internal-custom]
exten => *5432,1,NoOp(Servicio de Recordatorios)
 same => n,AGI(recordatorios.agi.php)
 same => n,Hangup()

exten => *5432#1*,1,NoOp(Crear recordatorio)
 same => n,AGI(recordatorios.agi.php,1)
 same => n,Hangup()

exten => *5432#2*,1,NoOp(Listar recordatorios)
 same => n,AGI(recordatorios.agi.php,2)
 same => n,Hangup()

exten => *5432#3*,1,NoOp(Modificar recordatorio)
 same => n,AGI(recordatorios.agi.php,3)
 same => n,Hangup()

exten => *5432#4*,1,NoOp(Borrar recordatorio)
 same => n,AGI(recordatorios.agi.php,4)
 same => n,Hangup()

[recordatorios-call]
exten => s,1,NoOp(Llamada de recordatorio)
 same => n,Dial(Local/\${DESTINO}@from-internal,30,U(recordatorios-playback^s^1(\${ID},\${AUDIO})))
 same => n,Hangup()

[recordatorios-playback]
exten => s,1,NoOp(Reproduccion de recordatorio)
 same => n,Playback(recordatorios/\${ARG2})
 same => n,AGI(recordatorios_estado.agi.php,ejecutado,\${ARG1})
 same => n,Return()
$fin
CONF;

    if ($agregar) {
        $contenido = is_file($archivo) ? file_get_contents($archivo) : '';
        if (strpos($contenido, $inicio) === false) {
            if ($contenido !== '' && substr($contenido, -1) !== "\n") {
                $contenido .= "\n";
            }
            $contenido .= "\n" . $bloque . "\n";
            file_put_contents($archivo, $contenido);
        }
        return;
    }

    if (!is_file($archivo)) {
        return;
    }

    $contenido = file_get_contents($archivo);
    $pattern = sprintf('/\n?%s.*?%s\n?/s', preg_quote($inicio, '/'), preg_quote($fin, '/'));
    $contenido = preg_replace($pattern, "\n", $contenido);
    file_put_contents($archivo, rtrim($contenido) . "\n");
}

function recordatorios_instalar_cron() {
    $archivo = '/etc/cron.d/recordatorios';
    $contenido = "* * * * * asterisk /usr/bin/php /var/www/html/admin/modules/recordatorios/cron/recordatorios_cron.php >> /var/log/recordatorios/cron.log 2>&1\n";
    file_put_contents($archivo, $contenido);
    @chmod($archivo, 0644);
}

function recordatorios_install() {
    global $db;

    $sql = "
    CREATE TABLE IF NOT EXISTS recordatorios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        posicion INT NOT NULL,
        fecha_recordatorio DATE NOT NULL,
        hora_recordatorio TIME NOT NULL,
        alertar_en DATETIME NOT NULL,
        destino_llamada VARCHAR(20) NOT NULL,
        archivo_audio VARCHAR(255) NOT NULL,
        estado ENUM('pendiente','procesando','alertado','cancelado','ejecutado') NOT NULL DEFAULT 'pendiente',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_alertar_en (alertar_en),
        INDEX idx_estado (estado),
        INDEX idx_fecha_hora (fecha_recordatorio, hora_recordatorio),
        INDEX idx_posicion (posicion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    $db->query($sql);

    $soundDir = recordatorios_menu_sonidos_dir();
    if (!is_dir($soundDir) && !mkdir($soundDir, 0755, true) && !is_dir($soundDir)) {
        error_log('recordatorios: no se pudo crear el directorio de sonidos durante la instalación: ' . $soundDir);
    }

    $logDir = '/var/log/recordatorios';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $agiDir = '/var/lib/asterisk/agi-bin';
    recordatorios_instalar_archivo(__DIR__ . '/agi/recordatorios.agi.php', $agiDir . '/recordatorios.agi.php');
    recordatorios_instalar_archivo(__DIR__ . '/agi/recordatorios_estado.agi.php', $agiDir . '/recordatorios_estado.agi.php');

    if (!is_executable($agiDir . '/recordatorios.agi.php') || !is_executable($agiDir . '/recordatorios_estado.agi.php')) {
        error_log('recordatorios: los AGI instalados no quedaron ejecutables en ' . $agiDir);
    }

    recordatorios_actualizar_dialplan(true);
    recordatorios_instalar_cron();

    if (!recordatorios_generar_sonidos_menu()) {
        error_log('recordatorios: la instalación terminó sin generar los sonidos del menú.');
    }
}

recordatorios_install();
