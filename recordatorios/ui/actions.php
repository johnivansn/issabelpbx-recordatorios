<?php if (recordatorios_puede_modificarse($r['id'])): ?>
    <a class="chip chip-edit" href="/admin/config.php?display=recordatorios&action=edit&id=<?= (int)$r['id'] ?>" aria-label="Editar recordatorio">
        <span class="chip-icon" aria-hidden="true">
            <svg viewBox="0 0 16 16" focusable="false" aria-hidden="true">
                <path d="M11.6 1.7a2 2 0 0 1 2.8 2.8l-7.9 7.9-3.6.8.8-3.6 7.9-7.9Zm-1 1L3.9 9.4l-.4 1.6 1.6-.4 6.7-6.7-1.2-1.2Z"></path>
            </svg>
        </span>
    </a>
    <a class="chip chip-delete" href="/admin/config.php?display=recordatorios&action=delete&id=<?= (int)$r['id'] ?>"
        onclick="return confirm('¿Seguro que deseas borrar este recordatorio?')" aria-label="Borrar recordatorio">
        <span class="chip-icon" aria-hidden="true">
            <svg viewBox="0 0 16 16" focusable="false" aria-hidden="true">
                <path d="M6.2 1.5h3.6l.5 1H14v1.5h-1l-.7 8.3a1.5 1.5 0 0 1-1.5 1.4H5.2a1.5 1.5 0 0 1-1.5-1.4L3 4H2V2.5h3.3l.5-1Zm1.1 2.5H5.1l.6 8h4.6l.6-8H7.3Zm-1 2h1.2v4H6.3v-4Zm2.2 0h1.2v4H8.5v-4Z"></path>
            </svg>
        </span>
    </a>
<?php else: ?>
    <span class="chip chip-lock">En proceso</span>
<?php endif; ?>
