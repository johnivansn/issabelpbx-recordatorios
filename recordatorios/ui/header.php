<style>
.recordatorios-shell {
    --bg: #f8f9fb;
    --surface: #ffffff;
    --surface-soft: #faf7ff;
    --ink: #2d2635;
    --muted: #6b6473;
    --line: #e5dff0;
    --line-strong: #d8cdea;
    --primary: #6f2dbd;
    --primary-soft: #efe3ff;
    --accent: #107868;
    --danger: #ba1e27;
    --warning: #a86600;
    --shadow: 0 10px 28px rgba(60, 37, 82, 0.08);
    color: var(--ink);
    padding-bottom: calc(var(--recordatorios-footer-space, 0px) + 32px);
}
.recordatorios-shell * {
    box-sizing: border-box;
}
.recordatorios-shell .recordatorios-hero {
    background:
        linear-gradient(180deg, rgba(255,255,255,.95), rgba(255,255,255,.92)),
        linear-gradient(135deg, #f1e8ff 0%, #eef7ff 100%);
    border: 1px solid var(--line);
    border-left: 5px solid var(--primary);
    border-radius: 18px;
    padding: 18px 20px;
    box-shadow: var(--shadow);
    margin-bottom: 16px;
    margin-top: 6px;
}
.recordatorios-shell .recordatorios-kicker {
    text-transform: uppercase;
    letter-spacing: .12em;
    font-size: 11px;
    color: var(--primary);
    font-weight: 800;
    margin-bottom: 6px;
}
.recordatorios-shell h1,
.recordatorios-shell h3 {
    margin: 0;
}
.recordatorios-shell h1 {
    font-size: 26px;
    font-weight: 700;
    line-height: 1.1;
    color: #3d3249;
}
.recordatorios-shell .recordatorios-subtitle {
    margin: 8px 0 0;
    color: var(--muted);
    max-width: 78ch;
    font-size: 15px;
    line-height: 1.45;
}
.recordatorios-shell .recordatorios-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 16px;
}
.recordatorios-shell .recordatorios-btn,
.recordatorios-shell .recordatorios-btn-secondary,
.recordatorios-shell .recordatorios-btn-danger,
.recordatorios-shell .chip,
.recordatorios-shell .form-actions input[type="submit"],
.recordatorios-shell .form-actions a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 999px;
    padding: 10px 15px;
    text-decoration: none;
    font-weight: 700;
    border: 1px solid transparent;
    transition: background .15s ease, transform .15s ease, box-shadow .15s ease, border-color .15s ease;
}
.recordatorios-shell .recordatorios-btn:hover,
.recordatorios-shell .recordatorios-btn-secondary:hover,
.recordatorios-shell .recordatorios-btn-danger:hover,
.recordatorios-shell .chip:hover,
.recordatorios-shell .form-actions input[type="submit"]:hover,
.recordatorios-shell .form-actions a:hover {
    transform: translateY(-1px);
}
.recordatorios-shell .recordatorios-btn {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 8px 20px rgba(111, 45, 189, .18);
}
.recordatorios-shell .recordatorios-btn-secondary {
    background: #fff;
    color: var(--primary);
    border-color: var(--line-strong);
}
.recordatorios-shell .recordatorios-btn-danger {
    background: #fff8f8;
    color: var(--danger);
    border-color: #f0c1c5;
}
.recordatorios-shell .recordatorios-status {
    margin-top: 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.recordatorios-shell .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 8px 12px;
    border: 1px solid var(--line);
    background: #fff;
    color: var(--muted);
    font-size: 13px;
}
.recordatorios-shell .status-pill strong {
    color: var(--ink);
}
.recordatorios-shell .status-pill.ok {
    border-color: #bfe4dd;
    background: #f2fbf9;
    color: var(--accent);
}
.recordatorios-shell .status-pill.warn {
    border-color: #f0d5a7;
    background: #fffaf1;
    color: var(--warning);
}
.recordatorios-shell .recordatorios-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-bottom: 24px;
}
.recordatorios-shell .recordatorios-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 18px;
    box-shadow: var(--shadow);
    padding: 18px;
}
.recordatorios-shell .recordatorios-card h3 {
    font-size: 18px;
    margin-bottom: 8px;
}
.recordatorios-shell .recordatorios-card p {
    color: var(--muted);
    margin: 0 0 10px;
    line-height: 1.5;
}
.recordatorios-shell .success,
.recordatorios-shell .error {
    border-radius: 14px;
    padding: 12px 14px;
    margin: 0 0 12px;
    border: 1px solid transparent;
    box-shadow: var(--shadow);
}
.recordatorios-shell .success {
    background: #f0fbf8;
    border-color: #bfe4dd;
    color: var(--accent);
}
.recordatorios-shell .error {
    background: #fff7f7;
    border-color: #f0c1c5;
    color: var(--danger);
}
.recordatorios-shell .recordatorios-table-wrap {
    overflow-x: auto;
}
.recordatorios-shell .recordatorios-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 780px;
}
.recordatorios-shell .recordatorios-table thead th {
    text-align: left;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--muted);
    padding: 12px 10px;
    border-bottom: 1px solid var(--line);
}
.recordatorios-shell .recordatorios-table tbody td {
    padding: 12px 10px;
    border-bottom: 1px solid #f1edf7;
    vertical-align: top;
}
.recordatorios-shell .recordatorios-table tbody tr:hover {
    background: #fcfbff;
}
.recordatorios-shell .recordatorios-empty {
    padding: 18px;
    text-align: center;
    color: var(--muted);
}
.recordatorios-shell .badge,
.recordatorios-shell .chip {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
}
.recordatorios-shell .badge {
    padding: 6px 10px;
}
.recordatorios-shell .badge-neutral {
    background: var(--primary-soft);
    color: var(--primary);
}
.recordatorios-shell .badge-state {
    border: 1px solid transparent;
}
.recordatorios-shell .badge-pendiente {
    background: #f2fbf9;
    color: var(--accent);
    border-color: #bfe4dd;
}
.recordatorios-shell .badge-procesando {
    background: #fffaf1;
    color: var(--warning);
    border-color: #f0d5a7;
}
.recordatorios-shell .badge-alertado,
.recordatorios-shell .badge-ejecutado {
    background: #eef2ff;
    color: #465fdb;
    border-color: #cad4ff;
}
.recordatorios-shell .badge-cancelado {
    background: #fff7f7;
    color: var(--danger);
    border-color: #f0c1c5;
}
.recordatorios-shell .chip {
    padding: 7px 12px;
    margin-right: 8px;
    color: var(--ink);
    background: #fff;
    border: 1px solid var(--line-strong);
    gap: 6px;
    white-space: nowrap;
}
.recordatorios-shell .chip-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 14px;
    height: 14px;
    flex: 0 0 14px;
}
.recordatorios-shell .chip-icon svg {
    width: 14px;
    height: 14px;
    fill: currentColor;
}
.recordatorios-shell .chip-edit {
    color: var(--primary);
}
.recordatorios-shell .chip-delete {
    color: var(--danger);
}
.recordatorios-shell .chip-lock {
    color: var(--muted);
}
.recordatorios-shell .recordatorios-audio-inline {
    width: min(260px, 100%);
    display: inline-grid;
    grid-template-columns: 34px minmax(0, 1fr) auto;
    align-items: center;
    gap: 8px;
}
.recordatorios-shell .recordatorios-audio-toggle {
    display: none;
    width: 34px;
    height: 34px;
    border: 1px solid var(--line-strong);
    border-radius: 999px;
    background: #fff;
    color: var(--primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
    box-shadow: 0 4px 12px rgba(111, 45, 189, .08);
}
.recordatorios-shell .recordatorios-audio-toggle:hover {
    transform: translateY(-1px);
}
.recordatorios-shell .recordatorios-audio-toggle.is-playing {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.recordatorios-shell .recordatorios-audio-icon {
    width: 14px;
    height: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.recordatorios-shell .recordatorios-audio-icon svg {
    width: 14px;
    height: 14px;
    fill: currentColor;
}
.recordatorios-shell .recordatorios-audio-track {
    min-width: 0;
    display: none;
    align-items: center;
}
.recordatorios-shell .recordatorios-audio-range {
    width: 100%;
    margin: 0;
    accent-color: var(--primary);
}
.recordatorios-shell .recordatorios-audio-native {
    width: 100%;
    display: block;
}
.recordatorios-shell .recordatorios-audio-inline audio[data-audio-source] {
    display: none;
}
.recordatorios-shell .recordatorios-audio-time {
    display: none;
    font-size: 12px;
    color: var(--muted);
    min-width: 38px;
    text-align: right;
}
.recordatorios-shell .recordatorios-audio-empty {
    color: var(--muted);
    font-size: 13px;
}
.recordatorios-shell .recordatorios-card--full {
    width: 100%;
}
.recordatorios-shell .recordatorios-form .form-group {
    margin-bottom: 14px;
}
.recordatorios-shell .recordatorios-form label {
    display: block;
    font-weight: 700;
    margin-bottom: 6px;
}
.recordatorios-shell .recordatorios-form input[type="text"],
.recordatorios-shell .recordatorios-form input[type="time"],
.recordatorios-shell .recordatorios-form input[type="file"] {
    width: 100%;
    border: 1px solid var(--line-strong);
    border-radius: 12px;
    padding: 11px 12px;
    background: #fff;
    color: var(--ink);
}
.recordatorios-shell .recordatorios-form small {
    display: block;
    margin-top: 6px;
    color: var(--muted);
}
.recordatorios-shell .form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
}
.recordatorios-shell .form-actions input[type="submit"] {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 8px 20px rgba(111, 45, 189, .18);
}
.recordatorios-shell .form-actions a {
    background: #fff;
    color: var(--primary);
    border-color: var(--line-strong);
}
.recordatorios-shell .recordatorios-audio {
    width: 100%;
    margin-top: 12px;
}
.recordatorios-shell .recordatorios-audio-inline.is-enhanced .recordatorios-audio-toggle {
    display: inline-flex;
}
.recordatorios-shell .recordatorios-audio-inline.is-enhanced .recordatorios-audio-track {
    display: flex;
}
.recordatorios-shell .recordatorios-audio-inline.is-enhanced .recordatorios-audio-time {
    display: inline-flex;
}
.recordatorios-shell .recordatorios-audio-inline.is-enhanced .recordatorios-audio-native {
    display: none;
}
@media (max-width: 960px) {
    .recordatorios-shell .recordatorios-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function () {
    var footerSelectors = [
        'footer',
        '#footer',
        '.footer',
        '.main-footer',
        '.page-footer',
        '.box-footer'
    ];

    function measureFooterSpace() {
        var footerHeight = 0;
        for (var i = 0; i < footerSelectors.length; i++) {
            var node = document.querySelector(footerSelectors[i]);
            if (node) {
                footerHeight = Math.max(footerHeight, Math.ceil(node.getBoundingClientRect().height));
            }
        }

        var nodes = document.querySelectorAll('body *');
        for (var j = 0; j < nodes.length; j++) {
            var el = nodes[j];
            var style = window.getComputedStyle(el);
            if (!style) {
                continue;
            }
            if (style.position !== 'fixed' && style.position !== 'sticky') {
                continue;
            }

            var rect = el.getBoundingClientRect();
            if (rect.height < 24) {
                continue;
            }

            var pinnedBottom = rect.bottom >= (window.innerHeight - 6);
            var bottomCss = style.bottom && style.bottom !== 'auto' ? parseFloat(style.bottom) : NaN;
            var hasBottomAnchor = !isNaN(bottomCss) && bottomCss <= 12;

            if (pinnedBottom || hasBottomAnchor) {
                footerHeight = Math.max(footerHeight, Math.ceil(rect.height));
            }
        }

        document.documentElement.style.setProperty('--recordatorios-footer-space', footerHeight + 'px');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', measureFooterSpace);
    } else {
        measureFooterSpace();
    }

    window.addEventListener('load', measureFooterSpace);
    window.addEventListener('resize', measureFooterSpace);

    function formatTime(seconds) {
        if (!isFinite(seconds) || seconds < 0) {
            seconds = 0;
        }
        var mins = Math.floor(seconds / 60);
        var secs = Math.floor(seconds % 60);
        return mins + ':' + String(secs).padStart(2, '0');
    }

    function initAudioPlayer(wrapper) {
        var audio = wrapper.querySelector('[data-audio-source]');
        var toggle = wrapper.querySelector('[data-audio-toggle]');
        var range = wrapper.querySelector('[data-audio-range]');
        var time = wrapper.querySelector('[data-audio-time]');

        if (!audio || !toggle || !range || !time) {
            return;
        }

        wrapper.classList.add('is-enhanced');

        function syncUi() {
            var duration = audio.duration || 0;
            var current = audio.currentTime || 0;
            range.max = duration > 0 ? String(Math.round(duration * 1000)) : '1000';
            range.value = duration > 0 ? String(Math.round(current * 1000)) : '0';
            time.textContent = formatTime(duration > 0 ? current : 0) + ' / ' + formatTime(duration > 0 ? duration : 0);
            toggle.classList.toggle('is-playing', !audio.paused && !audio.ended);
            toggle.setAttribute('aria-label', audio.paused ? 'Reproducir audio' : 'Pausar audio');
        }

        toggle.addEventListener('click', function () {
            if (audio.paused || audio.ended) {
                audio.play();
            } else {
                audio.pause();
            }
        });

        range.addEventListener('input', function () {
            if (!audio.duration) {
                return;
            }
            audio.currentTime = (parseInt(range.value, 10) || 0) / 1000;
            syncUi();
        });

        audio.addEventListener('loadedmetadata', syncUi);
        audio.addEventListener('timeupdate', syncUi);
        audio.addEventListener('play', syncUi);
        audio.addEventListener('pause', syncUi);
        audio.addEventListener('ended', function () {
            audio.currentTime = 0;
            syncUi();
        });

        syncUi();
    }

    function initAudioPlayers() {
        var players = document.querySelectorAll('[data-audio-player]');
        for (var i = 0; i < players.length; i++) {
            initAudioPlayer(players[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAudioPlayers);
    } else {
        initAudioPlayers();
    }
})();
</script>

<div class="recordatorios-shell">
    <div class="recordatorios-hero">
        <div class="recordatorios-kicker">IssabelPBX · Servicio de llamadas</div>
        <h1>Recordatorios Diarios</h1>
        <p class="recordatorios-subtitle">
            Gestiona recordatorios del día siguiente, sube o escucha audios y deja listo el aviso automático de 15 minutos antes.
        </p>
        <div class="recordatorios-toolbar">
            <a class="recordatorios-btn" href="/admin/config.php?display=recordatorios&action=new">Nuevo recordatorio</a>
            <a class="recordatorios-btn-secondary" href="/admin/config.php?display=recordatorios">Actualizar listado</a>
            <a class="recordatorios-btn-danger" href="/admin/config.php?display=recordatorios&action=regen_sounds">Regenerar sonidos</a>
        </div>
        <div class="recordatorios-status">
            <span class="status-pill <?= recordatorios_menu_sonidos_listos() ? 'ok' : 'warn' ?>">
                <strong>Sonidos</strong>
                <?= recordatorios_menu_sonidos_listos() ? 'generados' : 'pendientes de generar' ?>
            </span>
            <span class="status-pill">
                <strong>Audio</strong>
                WAV mono 8000 Hz
            </span>
        </div>
    </div>
