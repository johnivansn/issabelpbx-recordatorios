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

class RecordatoriosAgi {
    private $env = [];

    public function __construct() {
        while (($line = fgets(STDIN)) !== false) {
            $line = trim($line);
            if ($line === '') {
                break;
            }

            if (preg_match('/^([^:]+):\s*(.*)$/', $line, $m)) {
                $this->env[$m[1]] = $m[2];
            }
        }
    }

    private function command($command) {
        echo $command . "\n";
        flush();

        $response = fgets(STDIN);
        return $response === false ? '' : trim($response);
    }

    public function answer() {
        $this->command('ANSWER');
    }

    public function verbose($message, $level = 1) {
        $safe = str_replace(["\r", "\n"], ' ', (string) $message);
        $this->command('VERBOSE ' . escapeshellarg($safe) . ' ' . (int) $level);
    }

    public function streamFile($file, $escapeDigits = '') {
        $response = $this->command('STREAM FILE ' . $file . ' ' . $escapeDigits);
        return $this->parseResultValue($response);
    }

    public function waitForDigit($timeout = 5000) {
        $response = $this->command('WAIT FOR DIGIT ' . (int) $timeout);
        return $this->parseResultValue($response);
    }

    public function getData($file, $timeout = 5000, $maxDigits = 1) {
        $response = $this->command('GET DATA ' . $file . ' ' . (int) $timeout . ' ' . (int) $maxDigits);
        return $this->parseResultValue($response);
    }

    public function getDataRaw($file, $timeout = 5000, $maxDigits = 1) {
        return $this->command('GET DATA ' . $file . ' ' . (int) $timeout . ' ' . (int) $maxDigits);
    }

    public function sayDigits($digits) {
        $this->command('SAY DIGITS ' . preg_replace('/[^0-9]/', '', (string) $digits) . ' ""');
    }

    public function sayNumber($number) {
        $this->command('SAY NUMBER ' . (int) $number . ' ""');
    }

    public function recordFile($base, $format = 'wav', $escapeDigits = '#', $timeout = 60000) {
        $response = $this->command(
            'RECORD FILE ' . $base . ' ' . $format . ' "' . $escapeDigits . '" ' . (int) $timeout . ' 0 BEEP'
        );
        return $this->parseResultValue($response);
    }

    private function parseResultValue($response) {
        if (preg_match('/result=(-?\d+)/', $response, $m)) {
            return (int) $m[1];
        }

        return 0;
    }
}

function recordatorios_hora_desde_digitos($digitos) {
    $limpios = preg_replace('/[^0-9]/', '', (string) $digitos);
    if (strlen($limpios) !== 4) {
        return '';
    }

    $hora = (int) substr($limpios, 0, 2);
    $minuto = (int) substr($limpios, 2, 2);

    if ($hora < 0 || $hora > 23 || $minuto < 0 || $minuto > 59) {
        return '';
    }

    return sprintf('%02d:%02d', $hora, $minuto);
}

function recordatorios_trace($agi, $mensaje, $nivel = 2) {
    $agi->verbose($mensaje, $nivel);
}

function recordatorios_temp_dir() {
    $dir = '/var/lib/asterisk/sounds/recordatorios/tmp';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $dir;
}

function recordatorios_prompt($agi, $archivo) {
    $agi->verbose('Reproduciendo sonido de menu: recordatorios/menu/' . $archivo, 2);
    return $agi->streamFile('recordatorios/menu/' . $archivo, '12345#*');
}

function recordatorios_speech($agi, $archivo) {
    recordatorios_prompt($agi, $archivo);
}

function recordatorios_exito($agi) {
    recordatorios_trace($agi, 'Reproduciendo confirmacion de exito');
    recordatorios_speech($agi, 'exito');
}

function recordatorios_es_cancelacion_dtmf($valor) {
    $valor = trim((string) $valor);
    return $valor === '*' || $valor === '42';
}

function recordatorios_respuesta_dtmf($respuesta) {
    if (preg_match('/result=([^ ]+)/', (string) $respuesta, $m)) {
        return $m[1];
    }

    return '';
}

function recordatorios_leer_digitos_cancelables($agi, $prompt, $timeout, $maxDigits, $descripcion) {
    recordatorios_speech($agi, $prompt);
    recordatorios_trace($agi, 'Leyendo ' . $descripcion . ' con GET DATA timeout=' . (int) $timeout . ' maxDig=' . (int) $maxDigits);
    $respuesta = $agi->getDataRaw('beep', $timeout, $maxDigits);
    recordatorios_trace($agi, 'Respuesta cruda ' . $descripcion . '=' . $respuesta);
    $valor = recordatorios_respuesta_dtmf($respuesta);

    if (recordatorios_es_cancelacion_dtmf($valor)) {
        recordatorios_trace($agi, 'Cancelacion detectada en ' . $descripcion);
        return null;
    }

    return $valor;
}

function recordatorios_grabar_audio_cancelable($agi, $base, $duracionMs = 60000) {
    recordatorios_trace($agi, 'Grabando audio en base=' . $base);
    recordatorios_trace($agi, 'RecordFile escape=#* timeout=' . (int) $duracionMs . ' formato=wav');
    $agi->verbose('Grabando audio temporal en ' . $base . '.wav', 2);
    $resultadoGrabacion = $agi->recordFile($base, 'wav', '#*', $duracionMs);
    recordatorios_trace($agi, 'Resultado recordFile=' . $resultadoGrabacion);

    if (recordatorios_es_cancelacion_dtmf((string) $resultadoGrabacion)) {
        $audioCancelado = $base . '.wav';
        recordatorios_trace($agi, 'Grabacion cancelada por usuario, limpiando=' . $audioCancelado);
        if (is_file($audioCancelado)) {
            @unlink($audioCancelado);
        }
        return [null, true];
    }

    $audioTemporal = $base . '.wav';
    $existeAudioTemporal = is_file($audioTemporal);
    $tamanoAudioTemporal = $existeAudioTemporal ? filesize($audioTemporal) : 0;
    $agi->verbose(
        'Audio temporal creado: ' . $audioTemporal .
        ' existe=' . ($existeAudioTemporal ? 'si' : 'no') .
        ' bytes=' . $tamanoAudioTemporal,
        2
    );
    recordatorios_trace($agi, 'Audio temporal validacion existe=' . ($existeAudioTemporal ? 'si' : 'no') . ' bytes=' . $tamanoAudioTemporal);
    if (is_file($audioTemporal)) {
        @chmod($audioTemporal, 0644);
        recordatorios_trace($agi, 'Permisos aplicados al audio temporal=' . $audioTemporal);
    }

    return [is_file($audioTemporal) ? $audioTemporal : '', false];
}

function recordatorios_obtener_opcion_menu($agi, $primerCiclo = false) {
    if ($primerCiclo) {
        recordatorios_trace($agi, 'No vino opcion por argumento, reproduciendo bienvenida');
        $tecla = recordatorios_prompt($agi, 'bienvenida');
        recordatorios_trace($agi, 'Resultado bienvenida=' . $tecla);
        if ($tecla > 0) {
            $opcion = chr($tecla);
            recordatorios_trace($agi, 'Opcion capturada desde bienvenida=' . $opcion);
            return $opcion;
        }
    }

    recordatorios_trace($agi, 'Reproduciendo menu principal');
    $tecla = recordatorios_prompt($agi, 'menu_principal');
    recordatorios_trace($agi, 'Resultado menu principal=' . $tecla);
    if ($tecla > 0) {
        $opcion = chr($tecla);
        recordatorios_trace($agi, 'Opcion capturada desde menu=' . $opcion);
        return $opcion;
    }

    recordatorios_trace($agi, 'Esperando digitacion manual 10s');
    $tecla = (int) $agi->waitForDigit(10000);
    recordatorios_trace($agi, 'waitForDigit devolvio=' . $tecla);

    return $tecla > 0 ? chr($tecla) : '';
}

$agi = new RecordatoriosAgi();
$GLOBALS['recordatorios_verbose_logger'] = function ($mensaje, $nivel = 2) use ($agi) {
    $agi->verbose($mensaje, $nivel);
};
$agi->verbose('Iniciando AGI de recordatorios', 2);
$agi->answer();

$opcionInicial = $argv[1] ?? '';
recordatorios_trace($agi, 'Parametro de entrada opcion=' . ($opcionInicial !== '' ? $opcionInicial : '(vacío)'));

$ciclosSinRespuesta = 0;
$primerCiclo = true;
$maxCiclos = 6;

for ($ciclo = 0; $ciclo < $maxCiclos; $ciclo++) {
    $opcion = '';
    $salir = false;
    if ($ciclo === 0 && $opcionInicial !== '') {
        $opcion = (string) $opcionInicial;
        recordatorios_trace($agi, 'Procesando opcion inicial=' . $opcion);
    } else {
        $opcion = recordatorios_obtener_opcion_menu($agi, $primerCiclo);
        $primerCiclo = false;
    }

    if ($opcion === '') {
        $ciclosSinRespuesta++;
        recordatorios_trace($agi, 'Sin opcion capturada, contador=' . $ciclosSinRespuesta);
        recordatorios_speech($agi, 'error');
        if ($ciclosSinRespuesta >= 2) {
            recordatorios_trace($agi, 'Demasiados intentos sin respuesta, finalizando');
            break;
        }
        continue;
    }

    $ciclosSinRespuesta = 0;
    recordatorios_trace($agi, 'Procesando opcion=' . $opcion);

    switch ($opcion) {
        case '5':
            recordatorios_trace($agi, 'Salida solicitada por el usuario');
            $salir = true;
            break;

        case '1':
        recordatorios_trace($agi, 'Flujo crear recordatorio');
        recordatorios_speech($agi, 'solicita_destino');
        $destino = recordatorios_leer_digitos_cancelables($agi, 'solicita_destino', 10000, 20, 'destino');
        if ($destino === null) {
            recordatorios_trace($agi, 'Creacion cancelada en destino');
            recordatorios_speech($agi, 'cancelado');
            break;
        }
        recordatorios_trace($agi, 'Destino capturado=' . ($destino !== '' ? $destino : '(vacío)'));
        if (!recordatorios_validar_destino($destino)) {
            recordatorios_trace($agi, 'Destino invalido');
            recordatorios_speech($agi, 'error');
            break;
            }
            recordatorios_trace($agi, 'Destino valido=' . $destino);

        recordatorios_speech($agi, 'solicita_hora');
        $horaEntrada = recordatorios_leer_digitos_cancelables($agi, 'solicita_hora', 10000, 4, 'hora');
        if ($horaEntrada === null) {
            recordatorios_trace($agi, 'Creacion cancelada en hora');
            recordatorios_speech($agi, 'cancelado');
            break;
        }
        $hora = recordatorios_hora_desde_digitos($horaEntrada);
        recordatorios_trace($agi, 'Hora capturada=' . ($hora !== '' ? $hora : '(vacía)'));
        if ($hora === '' || !recordatorios_validar_hora($hora)) {
            recordatorios_trace($agi, 'Hora invalida');
            recordatorios_speech($agi, 'error');
            break;
        }
        recordatorios_trace($agi, 'Hora valida=' . $hora);

        recordatorios_speech($agi, 'solicita_audio');
        $base = '/var/lib/asterisk/sounds/recordatorios/recordatorio_' . date('Ymd_His') . '_' . random_int(1000, 9999);
        list($archivoAudio, $canceladoGrabacion) = recordatorios_grabar_audio_cancelable($agi, $base, 60000);
        if ($canceladoGrabacion) {
            recordatorios_trace($agi, 'Creacion cancelada durante la grabacion');
            recordatorios_speech($agi, 'cancelado');
            break;
        }
        recordatorios_trace($agi, 'Archivo audio final candidato=' . ($archivoAudio !== '' ? $archivoAudio : '(ninguno)'));
        if ($archivoAudio === '') {
            $agi->verbose('No se pudo guardar el audio grabado', 2);
            recordatorios_speech($agi, 'error');
            break;
            }

            try {
                recordatorios_trace($agi, 'Persistiendo recordatorio destino=' . $destino . ' hora=' . $hora . ' audio=' . $archivoAudio);
                recordatorios_crear($destino, $hora, $archivoAudio);
                recordatorios_trace($agi, 'Recordatorio creado correctamente');
                recordatorios_speech($agi, 'confirmacion_guardado');
            } catch (Exception $e) {
                recordatorios_trace($agi, 'Error al crear recordatorio: ' . $e->getMessage());
                recordatorios_speech($agi, 'error');
            }
            break;

        case '2':
            recordatorios_trace($agi, 'Flujo listar recordatorios');
            $recordatorios = recordatorios_listar();
            recordatorios_trace($agi, 'Cantidad de recordatorios=' . count($recordatorios));
            if (!$recordatorios) {
                recordatorios_trace($agi, 'No hay recordatorios para listar');
                recordatorios_speech($agi, 'error');
                break;
            }

            foreach ($recordatorios as $r) {
                recordatorios_trace($agi, 'Listando id=' . $r['id'] . ' posicion=' . $r['posicion'] . ' hora=' . $r['hora_recordatorio'] . ' destino=' . $r['destino_llamada']);
                $agi->sayNumber((int) $r['posicion']);
                $agi->sayDigits(str_replace(':', '', (string) $r['hora_recordatorio']));
                $agi->sayDigits($r['destino_llamada']);
            }
            break;

        case '3':
        recordatorios_trace($agi, 'Flujo editar recordatorio');
        recordatorios_speech($agi, 'solicita_posicion');
        $posicionEntrada = recordatorios_leer_digitos_cancelables($agi, 'solicita_posicion', 10000, 6, 'posicion');
        if ($posicionEntrada === null) {
            recordatorios_trace($agi, 'Edicion cancelada en posicion');
            recordatorios_speech($agi, 'cancelado');
            break;
        }
        $id = (int) $posicionEntrada;
        recordatorios_trace($agi, 'ID capturado para editar=' . $id);
        $recordatorio = $id > 0 ? recordatorios_get($id) : null;
        if (!$recordatorio || $recordatorio['estado'] !== 'pendiente') {
            recordatorios_trace($agi, 'Recordatorio no editable o no encontrado');
            recordatorios_speech($agi, 'error');
            break;
        }

        recordatorios_speech($agi, 'solicita_destino');
        $destino = recordatorios_leer_digitos_cancelables($agi, 'solicita_destino', 10000, 20, 'destino nuevo');
        if ($destino === null) {
            recordatorios_trace($agi, 'Edicion cancelada en destino');
            recordatorios_speech($agi, 'cancelado');
            break;
        }
        if ($destino === '' || $destino === '0') {
            $destino = (string) $recordatorio['destino_llamada'];
            recordatorios_trace($agi, 'Destino conservado=' . $destino);
        }
        recordatorios_trace($agi, 'Destino nuevo=' . ($destino !== '' ? $destino : '(vacío)'));
        if (!recordatorios_validar_destino($destino)) {
            recordatorios_trace($agi, 'Destino nuevo invalido');
            recordatorios_speech($agi, 'error');
            break;
        }
        recordatorios_trace($agi, 'Destino nuevo valido=' . $destino);

        recordatorios_speech($agi, 'solicita_hora');
        $horaEntrada = recordatorios_leer_digitos_cancelables($agi, 'solicita_hora', 10000, 4, 'hora nueva');
        if ($horaEntrada === null) {
            recordatorios_trace($agi, 'Edicion cancelada en hora');
            recordatorios_speech($agi, 'cancelado');
            break;
        }
        if ($horaEntrada === '' || $horaEntrada === '0') {
            $hora = substr((string) $recordatorio['hora_recordatorio'], 0, 5);
            recordatorios_trace($agi, 'Hora conservada=' . $hora);
        } else {
            $hora = recordatorios_hora_desde_digitos($horaEntrada);
        }
        recordatorios_trace($agi, 'Hora nueva=' . ($hora !== '' ? $hora : '(vacía)'));
        if ($hora === '' || !recordatorios_validar_hora($hora)) {
            recordatorios_trace($agi, 'Hora nueva invalida');
            recordatorios_speech($agi, 'error');
            break;
        }
        recordatorios_trace($agi, 'Hora nueva valida=' . $hora);

        recordatorios_speech($agi, 'solicita_audio_editar');
        $accionAudio = recordatorios_leer_digitos_cancelables($agi, 'solicita_audio_editar', 10000, 1, 'decision de audio');
        if ($accionAudio === null) {
            recordatorios_trace($agi, 'Edicion cancelada en decision de audio');
            recordatorios_speech($agi, 'cancelado');
            break;
        }

        $archivoAudio = null;
        if ($accionAudio === '' || $accionAudio === '0') {
            recordatorios_trace($agi, 'Audio conservado');
        } elseif ($accionAudio === '1') {
            recordatorios_speech($agi, 'solicita_audio');
            $base = '/var/lib/asterisk/sounds/recordatorios/recordatorio_' . date('Ymd_His') . '_' . random_int(1000, 9999);
            list($archivoAudio, $canceladoGrabacion) = recordatorios_grabar_audio_cancelable($agi, $base, 60000);
            if ($canceladoGrabacion) {
                recordatorios_trace($agi, 'Edicion cancelada durante la grabacion');
                recordatorios_speech($agi, 'cancelado');
                break;
            }
            recordatorios_trace($agi, 'Archivo editado candidato=' . ($archivoAudio !== '' ? $archivoAudio : '(ninguno)'));
            if ($archivoAudio === '') {
                $agi->verbose('No se pudo guardar el audio grabado', 2);
                recordatorios_speech($agi, 'error');
                break;
            }
        } else {
            recordatorios_trace($agi, 'Decision de audio invalida=' . $accionAudio);
            recordatorios_speech($agi, 'error');
            break;
        }

            try {
                recordatorios_trace($agi, 'Persistiendo actualizacion id=' . $id . ' destino=' . $destino . ' hora=' . $hora . ' audio=' . $archivoAudio);
                recordatorios_actualizar($id, $destino, $hora, $archivoAudio);
                recordatorios_trace($agi, 'Recordatorio actualizado correctamente');
                recordatorios_speech($agi, 'confirmacion_actualizado');
            } catch (Exception $e) {
                recordatorios_trace($agi, 'Error al actualizar recordatorio: ' . $e->getMessage());
                recordatorios_speech($agi, 'error');
            }
            break;

        case '4':
        recordatorios_trace($agi, 'Flujo borrar recordatorio');
        recordatorios_speech($agi, 'solicita_posicion');
        $posicionEntrada = recordatorios_leer_digitos_cancelables($agi, 'solicita_posicion', 10000, 6, 'posicion a borrar');
        if ($posicionEntrada === null) {
            recordatorios_trace($agi, 'Borrado cancelado en posicion');
            recordatorios_speech($agi, 'cancelado');
            break;
        }
        $id = (int) $posicionEntrada;
        recordatorios_trace($agi, 'ID capturado para borrar=' . $id);
        $recordatorio = $id > 0 ? recordatorios_get($id) : null;
        if (!$recordatorio || $recordatorio['estado'] !== 'pendiente') {
            recordatorios_trace($agi, 'Recordatorio no borrable o no encontrado');
            recordatorios_speech($agi, 'error');
            break;
        }

        recordatorios_speech($agi, 'solicita_confirmacion');
        $confirmacion = recordatorios_leer_digitos_cancelables($agi, 'solicita_confirmacion', 5000, 1, 'confirmacion borrado');
        if ($confirmacion === null) {
            recordatorios_trace($agi, 'Borrado cancelado por usuario');
            recordatorios_speech($agi, 'cancelado');
            break;
        }
        recordatorios_trace($agi, 'Confirmacion capturada=' . $confirmacion);
        if ($confirmacion === '1') {
            try {
                recordatorios_trace($agi, 'Eliminando recordatorio id=' . $id);
                recordatorios_eliminar($id);
                recordatorios_trace($agi, 'Recordatorio eliminado correctamente');
                recordatorios_speech($agi, 'confirmacion_eliminado');
            } catch (Exception $e) {
                recordatorios_trace($agi, 'Error al eliminar recordatorio: ' . $e->getMessage());
                recordatorios_speech($agi, 'error');
            }
        } else {
            recordatorios_trace($agi, 'Confirmacion invalida para borrado');
            recordatorios_speech($agi, 'error');
        }
        break;

        default:
            recordatorios_trace($agi, 'Opcion invalida, reproduciendo error');
            recordatorios_speech($agi, 'error');
            break;
    }

    if ($salir) {
        break;
    }
}

recordatorios_trace($agi, 'Finalizando flujo de llamada');
recordatorios_speech($agi, 'despedida');
