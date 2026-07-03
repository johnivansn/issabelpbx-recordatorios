<div class="recordatorios-card">
    <h3>Audio del recordatorio</h3>
    <p>Escucha la grabación asociada al registro seleccionado.</p>
    <audio class="recordatorios-audio" controls>
        <source src="/admin/config.php?display=recordatorios&action=play&id=<?= (int)$recordatorio['id'] ?>"
                type="audio/wav">
        Tu navegador no soporta reproducción de audio.
    </audio>
</div>
