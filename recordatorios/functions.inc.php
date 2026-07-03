<?php
if (!defined('ISSABELPBX_IS_AUTH')) {
    die('No direct script access allowed');
}

function recordatorios_db() {
    recordatorios_cargar_amp_conf();
    global $amp_conf;
    global $recordatorios_amp_conf_source;

    $host = $amp_conf['AMPDBHOST'] ?? 'localhost';
    $dbname = $amp_conf['AMPDBNAME'] ?? 'asterisk';
    $user = $amp_conf['AMPDBUSER'] ?? 'asteriskuser';
    $pass = $amp_conf['AMPDBPASS'] ?? '';

    recordatorios_log_debug(sprintf(
        'DB config source=%s host=%s db=%s user=%s pass=%s',
        $recordatorios_amp_conf_source ?: 'desconocido',
        $host,
        $dbname,
        $user,
        $pass !== '' ? 'si' : 'no'
    ));

    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}

function recordatorios_cargar_amp_conf() {
    global $amp_conf;
    global $recordatorios_amp_conf_source;

    if (isset($amp_conf) && is_array($amp_conf) && !empty($amp_conf['AMPDBNAME'])) {
        return $amp_conf;
    }

    $amp_conf = is_array($amp_conf ?? null) ? $amp_conf : [];
    $candidatos = [];

    $freepbxConf = getenv('FREEPBX_CONF');
    if (is_string($freepbxConf) && $freepbxConf !== '') {
        $candidatos[] = $freepbxConf;
    }

    $candidatos = array_merge($candidatos, [
        '/etc/freepbx.conf',
        '/etc/asterisk/freepbx.conf',
        '/etc/amportal.conf',
        '/etc/asterisk/amportal.conf',
    ]);

    foreach (array_unique($candidatos) as $archivo) {
        recordatorios_log_debug('evaluando config=' . $archivo);
        if (!is_string($archivo) || $archivo === '' || !is_readable($archivo)) {
            recordatorios_log_debug('config no legible=' . $archivo);
            continue;
        }

        try {
            $nombreArchivo = strtolower(basename($archivo));
            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));

            if ($nombreArchivo === 'freepbx.conf' || $extension === 'php') {
                $configuracion = recordatorios_parse_freepbx_php_conf($archivo);
                if (is_array($configuracion) && !empty($configuracion)) {
                    $amp_conf = array_merge($amp_conf, $configuracion);
                    recordatorios_log_debug('config PHP cargada=' . $archivo);
                }
                $recordatorios_amp_conf_source = $archivo;
            } elseif ($extension === 'conf') {
                $configuracion = recordatorios_parse_key_value_conf($archivo);
                if (is_array($configuracion) && !empty($configuracion)) {
                    $amp_conf = array_merge($amp_conf, $configuracion);
                    $recordatorios_amp_conf_source = $archivo;
                    recordatorios_log_debug('config CLAVE=VALOR cargada=' . $archivo);
                }
            }

            if (isset($amp_conf) && is_array($amp_conf) && !empty($amp_conf['AMPDBNAME'])) {
                recordatorios_log_debug('credenciales encontradas en=' . $recordatorios_amp_conf_source);
                return $amp_conf;
            }
            recordatorios_log_debug('sin credenciales utiles en=' . $archivo);
        } catch (Throwable $e) {
            error_log('recordatorios: no se pudo cargar la configuracion desde ' . $archivo . ': ' . $e->getMessage());
        }
    }

    return $amp_conf;
}

function recordatorios_log_debug($mensaje, $nivel = 2) {
    $mensaje = 'recordatorios: ' . (string) $mensaje;

    if (isset($GLOBALS['recordatorios_verbose_logger']) && is_callable($GLOBALS['recordatorios_verbose_logger'])) {
        try {
            call_user_func($GLOBALS['recordatorios_verbose_logger'], $mensaje, $nivel);
            return;
        } catch (Throwable $e) {
            error_log($mensaje . ' (logger AGI falló: ' . $e->getMessage() . ')');
            return;
        }
    }

    error_log($mensaje);
}

function recordatorios_audio_error($mensaje = null) {
    static $ultimoError = '';

    if (func_num_args() > 0) {
        $ultimoError = (string) $mensaje;
    }

    return $ultimoError;
}

function recordatorios_describir_error_subida_audio($codigo) {
    $codigo = (int) $codigo;

    $mensajes = [
        UPLOAD_ERR_INI_SIZE => 'El archivo supera el límite permitido por el servidor.',
        UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño permitido por el formulario.',
        UPLOAD_ERR_PARTIAL => 'La subida del archivo quedó incompleta.',
        UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo de audio.',
        UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene carpeta temporal para la subida.',
        UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo temporal.',
        UPLOAD_ERR_EXTENSION => 'Una extensión del servidor bloqueó la subida del audio.',
    ];

    return $mensajes[$codigo] ?? 'No se pudo subir el audio por un error desconocido.';
}

function recordatorios_audio_extensiones_aceptadas() {
    return ['wav', 'mp3', 'ogg', 'oga', 'flac', 'gsm'];
}

function recordatorios_audio_mimes_aceptados() {
    return [
        'audio/wav',
        'audio/x-wav',
        'audio/wave',
        'audio/mpeg',
        'audio/mp3',
        'audio/ogg',
        'audio/flac',
        'audio/gsm',
        'application/octet-stream',
    ];
}

function recordatorios_parse_freepbx_php_conf($archivo) {
    $contenido = @file_get_contents($archivo);
    if ($contenido === false || $contenido === '') {
        return [];
    }

    $configuracion = [];
    if (preg_match_all('/\\$amp_conf\\[\\s*[\'"]([^\'"]+)[\'"]\\s*\\]\\s*=\\s*([\'"])(.*?)\\2\\s*;/s', $contenido, $coincidencias, PREG_SET_ORDER)) {
        foreach ($coincidencias as $coincidencia) {
            $clave = $coincidencia[1];
            $valor = stripcslashes($coincidencia[3]);
            $configuracion[$clave] = $valor;
        }
    }

    return $configuracion;
}

function recordatorios_parse_key_value_conf($archivo) {
    $contenido = @file($archivo, FILE_IGNORE_NEW_LINES);
    if (!is_array($contenido) || empty($contenido)) {
        return [];
    }

    $configuracion = [];
    foreach ($contenido as $linea) {
        $linea = trim((string) $linea);
        if ($linea === '' || $linea[0] === '#' || $linea[0] === ';') {
            continue;
        }

        if (!preg_match('/^([A-Z0-9_]+)\s*=\s*(.*)$/', $linea, $m)) {
            continue;
        }

        $clave = trim($m[1]);
        $valor = trim($m[2]);
        $valor = trim($valor, "\"'");

        if ($clave !== '') {
            $configuracion[$clave] = stripcslashes($valor);
        }
    }

    return $configuracion;
}

function recordatorios_asegurar_directorio($dir) {
    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        return false;
    }

    @chmod($dir, 0777);
    return true;
}

function recordatorios_menu_sonidos_dir() {
    return '/var/lib/asterisk/sounds/recordatorios/menu';
}

function recordatorios_menu_sonido_path($archivo) {
    return recordatorios_menu_sonidos_dir() . '/' . basename((string) $archivo);
}

function recordatorios_generar_sonidos_menu() {
    $baseDir = recordatorios_menu_sonidos_dir();
    if (!is_dir($baseDir) && !mkdir($baseDir, 0755, true) && !is_dir($baseDir)) {
        error_log('recordatorios: no se pudo crear el directorio de sonidos del menú: ' . $baseDir);
        return false;
    }

    if (!is_writable($baseDir)) {
        error_log('recordatorios: el directorio de sonidos del menú no tiene permisos de escritura: ' . $baseDir);
        return false;
    }

    $sonidos = [
        'bienvenida' => 'Hola, le atiende el servicio de recordatorios. ¿Qué desea hacer?',
        'menu_principal' => 'Para crear un recordatorio marque uno. Para listar, marque dos. Para modificar, marque tres. Para borrar, marque cuatro. Para salir marque cinco. En cualquier paso puede cancelar con asterisco.',
        'solicita_destino' => 'Indique el destino del recordatorio. Para cancelar marque asterisco.',
        'solicita_hora' => 'Indique la hora del recordatorio. Para cancelar marque asterisco.',
        'solicita_audio' => 'Después del tono, grabe su recordatorio. Para cancelar marque asterisco y para finalizar marque numeral.',
        'solicita_audio_editar' => 'Para conservar el audio actual marque cero. Para grabar uno nuevo marque uno. Para cancelar marque asterisco.',
        'solicita_posicion' => 'Indique el número del recordatorio. Para cancelar marque asterisco.',
        'solicita_confirmacion' => 'Para confirmar, marque uno. Para cancelar marque asterisco.',
        'confirmacion_guardado' => 'Su recordatorio fue guardado correctamente.',
        'confirmacion_actualizado' => 'El recordatorio fue actualizado correctamente.',
        'confirmacion_eliminado' => 'El recordatorio fue eliminado.',
        'exito' => 'Operación completada con éxito.',
        'cancelado' => 'La operación fue cancelada.',
        'error' => 'La opción ingresada no es válida. Intente nuevamente.',
        'despedida' => 'Gracias por usar el servicio. Hasta luego.',
    ];

    $hasPico = trim(shell_exec('command -v pico2wave')) !== '';
    $hasSox = trim(shell_exec('command -v sox')) !== '';

    if (!$hasPico || !$hasSox) {
        error_log('recordatorios: faltan pico2wave o sox, no se generaron los sonidos del menú.');
        return false;
    }

    $generados = 0;
    foreach ($sonidos as $nombre => $texto) {
        $raw = "/tmp/{$nombre}_raw.wav";
        $tmp = "{$baseDir}/{$nombre}.tmp.wav";
        $out = "{$baseDir}/{$nombre}.wav";

        $cmd1 = 'pico2wave -l es-ES -w ' . escapeshellarg($raw) . ' ' . escapeshellarg($texto);
        exec($cmd1, $out1, $code1);
        if ($code1 !== 0) {
            @unlink($raw);
            error_log("recordatorios: no se pudo generar {$nombre}");
            continue;
        }

        $cmd2 = 'sox ' . escapeshellarg($raw) . ' -r 8000 -c 1 -e signed-integer -b 16 ' . escapeshellarg($tmp);
        exec($cmd2, $out2, $code2);
        @unlink($raw);

        if ($code2 !== 0) {
            @unlink($tmp);
            error_log("recordatorios: no se pudo convertir {$nombre}");
            continue;
        }

        if (@rename($tmp, $out)) {
            @chmod($out, 0644);
            $generados++;
        } else {
            @unlink($tmp);
            error_log("recordatorios: no se pudo mover {$nombre} al destino final");
        }
    }

    return $generados > 0;
}

function recordatorios_menu_sonidos_listos() {
    $baseDir = recordatorios_menu_sonidos_dir();
    foreach (recordatorios_menu_sonidos_archivos() as $archivo) {
        if (!is_file($baseDir . '/' . $archivo)) {
            return false;
        }
    }

    return true;
}

function recordatorios_menu_sonidos_archivos() {
    return [
        'bienvenida.wav',
        'menu_principal.wav',
        'solicita_destino.wav',
        'solicita_hora.wav',
        'solicita_audio.wav',
        'solicita_audio_editar.wav',
        'solicita_posicion.wav',
        'solicita_confirmacion.wav',
        'confirmacion_guardado.wav',
        'confirmacion_actualizado.wav',
        'confirmacion_eliminado.wav',
        'exito.wav',
        'cancelado.wav',
        'error.wav',
        'despedida.wav',
    ];
}

function recordatorios_listar() {
    $db = recordatorios_db();
    return $db->query("SELECT * FROM recordatorios ORDER BY posicion ASC, hora_recordatorio ASC, id ASC")->fetchAll();
}

function recordatorios_get($id) {
    $db = recordatorios_db();
    $stmt = $db->prepare("SELECT * FROM recordatorios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function recordatorios_validar_destino($destino) {
    return (bool) preg_match('/^[0-9]{3,20}$/', $destino);
}

function recordatorios_validar_hora($hora) {
    if (!preg_match('/^(\d{2}):(\d{2})(?::(\d{2}))?$/', $hora, $m)) {
        return false;
    }

    $hh = (int) $m[1];
    $mm = (int) $m[2];
    $ss = isset($m[3]) ? (int) $m[3] : 0;

    return $hh >= 0 && $hh <= 23 && $mm >= 0 && $mm <= 59 && $ss >= 0 && $ss <= 59;
}

function recordatorios_validar_audio(array $archivo) {
    if (empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
        recordatorios_log_debug('validacion audio: tmp_name invalido o no subido');
        recordatorios_audio_error('El archivo temporal del audio no existe o no llegó completo al servidor.');
        return false;
    }

    if (!empty($archivo['error']) && (int)$archivo['error'] !== UPLOAD_ERR_OK) {
        recordatorios_log_debug('validacion audio: error de upload=' . (int) $archivo['error']);
        recordatorios_audio_error(recordatorios_describir_error_subida_audio($archivo['error']));
        return false;
    }

    $nombre = strtolower((string)($archivo['name'] ?? ''));
    $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    if ($extension === '' || !in_array($extension, recordatorios_audio_extensiones_aceptadas(), true)) {
        recordatorios_log_debug('validacion audio: extension no aceptada=' . $nombre);
        recordatorios_audio_error('El formato del archivo no está entre los aceptados: WAV, MP3, OGG, FLAC o GSM.');
        return false;
    }

    $mime = function_exists('mime_content_type') ? @mime_content_type($archivo['tmp_name']) : '';
    $esValido = in_array($mime, recordatorios_audio_mimes_aceptados(), true);
    recordatorios_log_debug('validacion audio: nombre=' . $nombre . ' mime=' . ($mime !== '' ? $mime : '(vacio)') . ' valido=' . ($esValido ? 'si' : 'no'));
    if (!$esValido) {
        recordatorios_audio_error('El archivo se subió, pero su tipo MIME no parece compatible con los formatos que el servidor convierte sin problemas.');
    }
    return $esValido;
}

function recordatorios_guardar_audio(array $archivo) {
    if (!recordatorios_validar_audio($archivo)) {
        recordatorios_log_debug('guardar audio: archivo no valido');
        return '';
    }

    $baseDir = '/var/lib/asterisk/sounds/recordatorios';
    if (!recordatorios_asegurar_directorio($baseDir)) {
        recordatorios_log_debug('guardar audio: no se pudo crear directorio=' . $baseDir);
        return '';
    }

    $nombre = 'recordatorio_' . date('Ymd_His') . '_' . random_int(1000, 9999) . '.wav';
    $raw = $baseDir . '/' . $nombre . '.raw';
    $destino = $baseDir . '/' . $nombre;
    recordatorios_log_debug('guardar audio: tmp=' . ($archivo['tmp_name'] ?? '(sin tmp)') . ' raw=' . $raw . ' destino=' . $destino);

    if (!move_uploaded_file($archivo['tmp_name'], $raw)) {
        recordatorios_log_debug('guardar audio: move_uploaded_file fallo');
        recordatorios_audio_error('No se pudo mover el archivo subido al directorio temporal del módulo.');
        return '';
    }

    $destinoFinal = recordatorios_convertir_audio_a_wav($raw, $destino);
    @unlink($raw);

    recordatorios_log_debug('guardar audio: conversion final=' . ($destinoFinal !== '' ? $destinoFinal : '(fallo)'));
    if ($destinoFinal === '') {
        recordatorios_audio_error('El audio llegó al servidor, pero no se pudo convertir a WAV mono 8000 Hz.');
    }

    return $destinoFinal;
}

function recordatorios_guardar_audio_desde_ruta($rutaOrigen, $convertir = true) {
    if (!is_string($rutaOrigen) || $rutaOrigen === '' || !is_file($rutaOrigen)) {
        recordatorios_log_debug('guardar audio ruta: origen no valido=' . (string) $rutaOrigen);
        recordatorios_audio_error('El audio grabado por la llamada no existe en la ruta esperada.');
        return '';
    }

    $baseDir = '/var/lib/asterisk/sounds/recordatorios';
    if (!recordatorios_asegurar_directorio($baseDir)) {
        recordatorios_log_debug('guardar audio ruta: no se pudo crear directorio=' . $baseDir);
        recordatorios_audio_error('No se pudo crear el directorio donde se guardan los audios del módulo.');
        return '';
    }

    $nombre = 'recordatorio_' . date('Ymd_His') . '_' . random_int(1000, 9999) . '.wav';
    $destino = $baseDir . '/' . $nombre;
    recordatorios_log_debug('guardar audio ruta: origen=' . $rutaOrigen . ' destino=' . $destino . ' convertir=' . ($convertir ? 'si' : 'no'));

    if ($convertir === false) {
        if (@rename($rutaOrigen, $destino)) {
            @chmod($destino, 0644);
            recordatorios_log_debug('guardar audio ruta: rename exitoso');
            return $destino;
        }

        if (@copy($rutaOrigen, $destino)) {
            @unlink($rutaOrigen);
            @chmod($destino, 0644);
            recordatorios_log_debug('guardar audio ruta: copy exitoso');
            return $destino;
        }

        recordatorios_log_debug('guardar audio ruta: no se pudo mover archivo');
        recordatorios_audio_error('No se pudo mover el audio grabado a la carpeta final del módulo.');
        return '';
    }

    $resultado = recordatorios_convertir_audio_a_wav($rutaOrigen, $destino);
    if ($resultado === '') {
        recordatorios_audio_error('El audio grabado por la llamada no pudo convertirse a WAV compatible.');
    }
    return $resultado;
}

function recordatorios_convertir_audio_a_wav($origen, $destino) {
    $sox = trim((string) shell_exec('command -v sox'));

    if ($sox !== '') {
        $cmd = 'sox ' . escapeshellarg($origen) . ' -r 8000 -c 1 -e signed-integer -b 16 ' . escapeshellarg($destino) . ' 2>&1';
        recordatorios_log_debug('convertir audio: metodo=sox cmd=' . $cmd);
        exec($cmd, $out, $code);
        recordatorios_log_debug('convertir audio: salida sox=' . json_encode($out) . ' code=' . (int) $code);
    } else {
        recordatorios_audio_error('El servidor no tiene sox para convertir el audio.');
        return '';
    }

    recordatorios_log_debug('convertir audio: destino=' . $destino . ' existe=' . (is_file($destino) ? 'si' : 'no') . ' bytes=' . (is_file($destino) ? (string) filesize($destino) : '0'));

    if ($code !== 0) {
        recordatorios_audio_error('El conversor del servidor devolvió un error al normalizar el audio.');
        return '';
    }

    if (!is_file($destino) || filesize($destino) === 0) {
        recordatorios_audio_error('La conversión terminó, pero el archivo WAV no quedó creado o quedó vacío.');
        return '';
    }

    return $destino;
}

function recordatorios_calcular_posicion($hora, $db = null) {
    $db = $db ?: recordatorios_db();
    $stmt = $db->prepare("
        SELECT id
        FROM recordatorios
        WHERE estado = 'pendiente'
          AND hora_recordatorio <= ?
        ORDER BY hora_recordatorio ASC, id ASC
        FOR UPDATE
    ");
    $stmt->execute([$hora]);
    return count($stmt->fetchAll()) + 1;
}

function recordatorios_reordenar_con_db($db) {
    $rows = $db->query("
        SELECT id
        FROM recordatorios
        WHERE estado = 'pendiente'
        ORDER BY hora_recordatorio ASC, id ASC
        FOR UPDATE
    ")->fetchAll();

    $pos = 1;
    foreach ($rows as $row) {
        $stmt = $db->prepare("UPDATE recordatorios SET posicion = ? WHERE id = ?");
        $stmt->execute([$pos, $row['id']]);
        $pos++;
    }
}

function recordatorios_reordenar() {
    $db = recordatorios_db();
    $db->beginTransaction();
    try {
        recordatorios_reordenar_con_db($db);
        $db->commit();
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function recordatorios_crear($destino, $hora, $archivoAudio) {
    $db = recordatorios_db();
    if (!recordatorios_validar_destino($destino)) {
        throw new InvalidArgumentException('Destino inválido');
    }
    if (!recordatorios_validar_hora($hora)) {
        throw new InvalidArgumentException('Hora inválida');
    }

    $db->beginTransaction();
    try {
        $fecha = date('Y-m-d', strtotime('+1 day'));
        $alertarEn = date('Y-m-d H:i:s', strtotime($fecha . ' ' . $hora . ' -15 minutes'));
        $posicion = recordatorios_calcular_posicion($hora, $db);
        recordatorios_log_debug('crear: fecha=' . $fecha . ' hora=' . $hora . ' alertar_en=' . $alertarEn . ' posicion=' . $posicion . ' audio=' . $archivoAudio);

        $stmt = $db->prepare("
            INSERT INTO recordatorios
            (posicion, fecha_recordatorio, hora_recordatorio, alertar_en, destino_llamada, archivo_audio, estado)
            VALUES (?, ?, ?, ?, ?, ?, 'pendiente')
        ");
        $stmt->execute([$posicion, $fecha, $hora, $alertarEn, $destino, $archivoAudio]);
        recordatorios_log_debug('crear: insert ok id=' . (string) $db->lastInsertId());

        $db->commit();
        return (int)$db->lastInsertId();
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function recordatorios_eliminar($id) {
    $db = recordatorios_db();
    $db->beginTransaction();
    try {
        recordatorios_log_debug('eliminar: buscando id=' . (int) $id);
        $stmt = $db->prepare("SELECT estado, archivo_audio FROM recordatorios WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row || $row['estado'] !== 'pendiente') {
            throw new RuntimeException('No se puede borrar un recordatorio en proceso.');
        }

        $stmt = $db->prepare("DELETE FROM recordatorios WHERE id = ?");
        $stmt->execute([$id]);
        recordatorios_log_debug('eliminar: borrado db id=' . (int) $id);

        recordatorios_reordenar_con_db($db);
        $db->commit();

        if (!empty($row['archivo_audio'])) {
            recordatorios_log_debug('eliminar: limpiando audio=' . $row['archivo_audio']);
            recordatorios_eliminar_audio($row['archivo_audio']);
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function recordatorios_puede_modificarse($id) {
    $db = recordatorios_db();
    $stmt = $db->prepare("SELECT estado FROM recordatorios WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row && $row['estado'] === 'pendiente';
}

function recordatorios_marcar_estado($id, $estado) {
    $db = recordatorios_db();
    $stmt = $db->prepare("UPDATE recordatorios SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);
    return $stmt->rowCount() > 0;
}

function recordatorios_marcar_procesando($id) {
    $db = recordatorios_db();
    $stmt = $db->prepare("
        UPDATE recordatorios
        SET estado = 'procesando'
        WHERE id = ? AND estado = 'pendiente'
    ");
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

function recordatorios_recuperar_procesando_estancado($db) {
    $stmt = $db->prepare("
        SELECT id
        FROM recordatorios
        WHERE estado = 'procesando'
          AND updated_at < (NOW() - INTERVAL 15 MINUTE)
        FOR UPDATE
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $stmt = $db->prepare("UPDATE recordatorios SET estado = 'alertado' WHERE id = ?");
        $stmt->execute([$row['id']]);
    }
}

function recordatorios_marcar_alertado($id) {
    $db = recordatorios_db();
    $stmt = $db->prepare("UPDATE recordatorios SET estado = 'alertado' WHERE id = ?");
    $stmt->execute([$id]);
}

function recordatorios_marcar_ejecutado($id) {
    $db = recordatorios_db();
    $stmt = $db->prepare("UPDATE recordatorios SET estado = 'ejecutado' WHERE id = ?");
    $stmt->execute([$id]);
}

function recordatorios_actualizar($id, $destino, $hora, $archivoAudio = null) {
    if (!recordatorios_validar_hora($hora)) {
        throw new InvalidArgumentException('Hora inválida');
    }
    if (!recordatorios_validar_destino($destino)) {
        throw new InvalidArgumentException('Destino inválido');
    }

    $db = recordatorios_db();
    $db->beginTransaction();
    try {
        recordatorios_log_debug('actualizar: buscando id=' . (int) $id . ' destino=' . $destino . ' hora=' . $hora . ' nuevo_audio=' . ($archivoAudio ?: '(sin cambio)'));
        $stmt = $db->prepare("SELECT estado, archivo_audio FROM recordatorios WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new RuntimeException('Recordatorio no encontrado');
        }

        if ($row['estado'] !== 'pendiente') {
            throw new RuntimeException('No se puede editar un recordatorio en proceso.');
        }

        $fecha = date('Y-m-d', strtotime('+1 day'));
        $alertarEn = date('Y-m-d H:i:s', strtotime($fecha . ' ' . $hora . ' -15 minutes'));

        if ($archivoAudio) {
            $stmt = $db->prepare("
                UPDATE recordatorios
                SET destino_llamada = ?, hora_recordatorio = ?, alertar_en = ?, archivo_audio = ?, estado = 'pendiente'
                WHERE id = ?
            ");
            $stmt->execute([$destino, $hora, $alertarEn, $archivoAudio, $id]);
            recordatorios_log_debug('actualizar: audio reemplazado id=' . (int) $id . ' archivo=' . $archivoAudio);
        } else {
            $stmt = $db->prepare("
                UPDATE recordatorios
                SET destino_llamada = ?, hora_recordatorio = ?, alertar_en = ?, estado = 'pendiente'
                WHERE id = ?
            ");
            $stmt->execute([$destino, $hora, $alertarEn, $id]);
            recordatorios_log_debug('actualizar: audio conservado id=' . (int) $id);
        }

        $db->commit();

        if (!empty($row['archivo_audio']) && $archivoAudio) {
            recordatorios_log_debug('actualizar: limpiando audio anterior=' . $row['archivo_audio']);
            recordatorios_eliminar_audio($row['archivo_audio']);
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function recordatorios_eliminar_audio($rutaAudio) {
    if ($rutaAudio && file_exists($rutaAudio)) {
        recordatorios_log_debug('eliminar audio: borrando=' . $rutaAudio . ' bytes=' . filesize($rutaAudio));
        unlink($rutaAudio);
        return;
    }

    recordatorios_log_debug('eliminar audio: no existe=' . (string) $rutaAudio);
}
