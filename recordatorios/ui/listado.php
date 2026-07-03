<div class="recordatorios-grid">
    <div class="recordatorios-card recordatorios-card--full">
        <h3>Listado</h3>
        <p>Recordatorios cargados en el sistema y listos para gestionar.</p>

        <div class="recordatorios-table-wrap">
            <table class="recordatorios-table">
                <thead>
                    <tr>
                        <th>Posición</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Audio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($recordatorios)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="recordatorios-empty">
                                Todavía no hay recordatorios creados.
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recordatorios as $r): ?>
                        <tr>
                            <td><span class="badge badge-neutral"><?= htmlspecialchars($r['posicion']) ?></span></td>
                            <td><?= htmlspecialchars($r['fecha_recordatorio']) ?></td>
                            <td><?= htmlspecialchars($r['hora_recordatorio']) ?></td>
                            <td><?= htmlspecialchars($r['destino_llamada']) ?></td>
                            <td>
                                <span class="badge badge-state badge-<?= htmlspecialchars($r['estado']) ?>">
                                    <?= htmlspecialchars($r['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($r['archivo_audio'])): ?>
                                    <div class="recordatorios-audio-inline" data-audio-player>
                                        <button type="button" class="recordatorios-audio-toggle" data-audio-toggle aria-label="Reproducir audio">
                                            <span class="recordatorios-audio-icon" aria-hidden="true">
                                                <svg viewBox="0 0 16 16" focusable="false" aria-hidden="true">
                                                    <path d="M5 3.4v9.2L12.6 8 5 3.4Z"></path>
                                                </svg>
                                            </span>
                                        </button>
                                        <div class="recordatorios-audio-track">
                                            <input type="range" min="0" max="1000" value="0" step="1"
                                                class="recordatorios-audio-range" data-audio-range aria-label="Progreso del audio">
                                        </div>
                                        <span class="recordatorios-audio-time" data-audio-time>0:00</span>
                                        <audio class="recordatorios-audio-native" controls preload="none">
                                            <source src="/admin/config.php?display=recordatorios&action=play&id=<?= (int)$r['id'] ?>"
                                                    type="audio/wav">
                                            Tu navegador no soporta reproducción de audio.
                                        </audio>
                                        <audio data-audio-source preload="none">
                                            <source src="/admin/config.php?display=recordatorios&action=play&id=<?= (int)$r['id'] ?>"
                                                    type="audio/wav">
                                            Tu navegador no soporta reproducción de audio.
                                        </audio>
                                    </div>
                                <?php else: ?>
                                    <span class="recordatorios-audio-empty">Sin audio</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php include __DIR__ . '/actions.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
