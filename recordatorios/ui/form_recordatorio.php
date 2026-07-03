<div class="recordatorios-grid">
    <div class="recordatorios-card">
        <h3><?= ($action ?? 'list') === 'new' ? 'Crear recordatorio' : 'Editar recordatorio' ?></h3>
        <p>Completa el formulario y guarda el audio en formato WAV para que quede listo para la llamada automática.</p>

        <form method="post" enctype="multipart/form-data" class="recordatorios-form">
            <div class="form-group">
                <label for="destino_llamada">Destino</label>
                <input type="text" name="destino_llamada" id="destino_llamada" placeholder="Ej. 1001"
                    value="<?= htmlspecialchars($recordatorio['destino_llamada'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="hora_recordatorio">Hora</label>
                <input type="time" name="hora_recordatorio" id="hora_recordatorio"
                    value="<?= htmlspecialchars($recordatorio['hora_recordatorio'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="audio">Grabación</label>
                <input type="file" name="audio" id="audio" accept=".wav,.mp3,.ogg,.oga,.flac,.gsm,audio/*" <?= ($action ?? 'list') === 'new' ? 'required' : '' ?>>
                <small>Formatos recomendados: WAV, MP3, OGG/Vorbis, FLAC o GSM. El servidor los convertirá a WAV mono 8000 Hz para Asterisk usando SoX.</small>
            </div>

            <div class="form-actions">
                <input class="recordatorios-btn" type="submit" value="Guardar">
                <a class="recordatorios-btn-secondary" href="/admin/config.php?display=recordatorios">Cancelar</a>
            </div>
        </form>
    </div>

    <div class="recordatorios-card">
        <h3>Consejos</h3>
        <p>Si el audio no se generó al instalar, usa el botón <strong>Regenerar sonidos</strong> en la cabecera.</p>
        <p>El módulo trabaja con recordatorios del día siguiente y llama 15 minutos antes de la hora configurada.</p>
    </div>
</div>
