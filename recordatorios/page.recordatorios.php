<?php
if (!defined('ISSABELPBX_IS_AUTH')) {
    die('No direct script access allowed');
}

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destino = trim($_POST['destino_llamada'] ?? '');
    $hora = trim($_POST['hora_recordatorio'] ?? '');
    $archivoAudio = '';
    $audioSubido = !empty($_FILES['audio']['tmp_name']);

    try {
        if (!recordatorios_validar_hora($hora)) {
            throw new InvalidArgumentException('La hora no tiene un formato válido.');
        }

        if (!recordatorios_validar_destino($destino)) {
            throw new InvalidArgumentException('El destino no tiene un formato válido.');
        }

        if ($action === 'new' || $audioSubido) {
            $uploadError = (int) ($_FILES['audio']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($uploadError !== UPLOAD_ERR_OK) {
                throw new RuntimeException('No se pudo subir el audio: ' . recordatorios_describir_error_subida_audio($uploadError));
            }

            $archivoAudio = recordatorios_guardar_audio($_FILES['audio'] ?? []);
            if ($archivoAudio === '') {
                $detalleAudio = recordatorios_audio_error();
                throw new RuntimeException($detalleAudio !== '' ? $detalleAudio : 'No se pudo guardar el audio. Revisa el formato WAV y los permisos del servidor.');
            }
        }

        if ($action === 'new') {
            if ($archivoAudio === '') {
                throw new RuntimeException('Debes subir una grabación WAV válida.');
            }
            recordatorios_crear($destino, $hora, $archivoAudio);
            $mensaje = 'Recordatorio creado correctamente.';
            $action = 'list';
        }

        if ($action === 'edit' && $id > 0) {
            recordatorios_actualizar($id, $destino, $hora, $archivoAudio ?: null);
            $mensaje = 'Recordatorio actualizado correctamente.';
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        recordatorios_log_debug('page post: action=' . $action . ' id=' . $id . ' error=' . $e->getMessage());
    }
}

if ($action === 'regen_sounds') {
    try {
        if (recordatorios_generar_sonidos_menu()) {
            $mensaje = 'Los sonidos del menú se regeneraron correctamente.';
        } else {
            $error = 'No fue posible generar los sonidos. Verifica pico2wave y sox en el servidor.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        recordatorios_log_debug('page regen_sounds: error=' . $e->getMessage());
    }
    $action = 'list';
}

if ($action === 'delete' && $id > 0) {
    try {
        recordatorios_eliminar($id);
        $mensaje = 'Recordatorio eliminado correctamente.';
        $action = 'list';
    } catch (Exception $e) {
        $error = $e->getMessage();
        recordatorios_log_debug('page delete: id=' . $id . ' error=' . $e->getMessage());
    }
}

$recordatorios = [];
$recordatorio = [];

try {
    $recordatorios = recordatorios_listar();
    $recordatorio = $id > 0 ? recordatorios_get($id) : [];
    recordatorios_log_debug('page: action=' . $action . ' id=' . $id . ' recordatorios=' . count($recordatorios));
} catch (Exception $e) {
    $error = $error ?: $e->getMessage();
    recordatorios_log_debug('page: error al listar=' . $e->getMessage());
}

if ($action === 'play' && $id > 0 && !empty($recordatorio['archivo_audio'])) {
    $rutaAudio = $recordatorio['archivo_audio'];
    recordatorios_log_debug('page play: id=' . $id . ' ruta=' . $rutaAudio);
    if (is_file($rutaAudio)) {
        $bytes = filesize($rutaAudio);
        if ($bytes <= 0) {
            recordatorios_log_debug('page play: archivo vacío=' . $rutaAudio);
            http_response_code(422);
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'El archivo de audio existe, pero está vacío.';
            exit;
        }

        recordatorios_log_debug('page play: archivo encontrado bytes=' . $bytes);
        header('Content-Type: audio/wav');
        header('Content-Length: ' . $bytes);
        header('Content-Disposition: inline; filename="' . basename($rutaAudio) . '"');
        readfile($rutaAudio);
        exit;
    }

    recordatorios_log_debug('page play: archivo no existe o no es legible=' . $rutaAudio);
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'No se encontró el archivo de audio o no se puede leer desde disco.';
    exit;
} elseif ($action === 'play') {
    recordatorios_log_debug('page play: no hay audio para id=' . $id);
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'No hay audio asociado al recordatorio solicitado.';
    exit;
}
?>

<?php include __DIR__ . '/ui/header.php'; ?>

<div class="container-fluid recordatorios-page">
    <?php if ($mensaje !== ''): ?>
        <div class="success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($action === 'new' || $action === 'edit'): ?>
        <?php include __DIR__ . '/ui/form_recordatorio.php'; ?>
    <?php elseif ($action === 'play' && $id > 0 && !empty($recordatorio)): ?>
        <?php include __DIR__ . '/ui/audio_player.php'; ?>
    <?php else: ?>
        <?php include __DIR__ . '/ui/listado.php'; ?>
    <?php endif; ?>

</div>
</div>
