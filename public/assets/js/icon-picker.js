/**
 * Icon Picker - seletor visual de ícones (Bootstrap Icons).
 * Uso: adicione um input com a classe "icon-picker-input".
 * O componente cria um botão de pré-visualização e um popover com busca.
 */
(function () {
    // Conjunto curado de ícones úteis para salas/itens (nomes sem o prefixo "bi-")
    const ICONS = [
        'easel', 'easel2', 'shop', 'shop-window', 'building', 'buildings', 'house',
        'house-door', 'house-heart', 'house-gear', 'door-open', 'door-closed',
        'people', 'people-fill', 'person', 'person-badge', 'person-video3', 'person-workspace',
        'projector', 'tv', 'display', 'pc-display', 'camera-video', 'webcam', 'film',
        'speaker', 'soundwave', 'music-note-beamed', 'mic', 'headphones', 'volume-up',
        'lightbulb', 'lamp', 'brightness-high', 'stars', 'star', 'award', 'trophy', 'gem',
        'cup-hot', 'cup-straw', 'egg-fried', 'basket', 'bag', 'box-seam', 'boxes',
        'columns-gap', 'grid-3x3-gap', 'layout-wtf', 'kanban', 'table',
        'rulers', 'pencil', 'palette', 'brush', 'vector-pen',
        'briefcase', 'clipboard-check', 'list-check', 'check2-circle', 'check2-square',
        'calendar-event', 'calendar-check', 'clock', 'hourglass-split',
        'geo-alt', 'map', 'pin-map', 'signpost', 'cone-striped',
        'wifi', 'router', 'phone', 'tablet', 'laptop', 'keyboard', 'mouse',
        'gear', 'tools', 'wrench', 'lightning-charge', 'plug', 'battery-charging',
        'hand-index', 'hand-thumbs-up', 'chat-dots', 'chat-left-text', 'megaphone',
        'gift', 'balloon', 'emoji-smile', 'heart', 'fire', 'flower1', 'tree',
        'car-front', 'truck', 'airplane', 'bicycle', 'compass', 'globe',
        'shield-check', 'lock', 'key', 'camera', 'image', 'card-image',
        'book', 'journal-text', 'file-earmark-text', 'folder', 'archive'
    ];

    function createPicker(input) {
        input.type = 'hidden'; // esconde o campo original de texto

        // Container
        const wrap = document.createElement('div');
        wrap.className = 'icon-picker';

        // Botão de pré-visualização
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'icon-picker-btn form-control d-flex align-items-center gap-2';
        wrap.appendChild(btn);

        // Painel (dropdown)
        const panel = document.createElement('div');
        panel.className = 'icon-picker-panel';
        panel.innerHTML =
            '<input type="text" class="form-control form-control-sm icon-picker-search" placeholder="Buscar ícone...">' +
            '<div class="icon-picker-grid"></div>';
        wrap.appendChild(panel);

        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        const grid = panel.querySelector('.icon-picker-grid');
        const search = panel.querySelector('.icon-picker-search');

        function normalize(name) {
            // Aceita "bi-easel" ou "easel"
            return (name || '').replace(/^bi-/, '').trim();
        }

        function renderPreview() {
            const icon = normalize(input.value) || 'easel';
            btn.innerHTML = '<i class="bi bi-' + icon + '"></i><span class="icon-picker-label">bi-' + icon + '</span>' +
                '<i class="bi bi-chevron-down ms-auto text-muted"></i>';
        }

        function renderGrid(filter) {
            const f = (filter || '').toLowerCase();
            const list = ICONS.filter(i => !f || i.includes(f));
            const current = normalize(input.value);
            grid.innerHTML = list.map(i =>
                '<button type="button" class="icon-picker-item' + (i === current ? ' active' : '') + '" ' +
                'data-icon="' + i + '" title="bi-' + i + '"><i class="bi bi-' + i + '"></i></button>'
            ).join('') || '<div class="text-muted small p-2">Nenhum ícone encontrado.</div>';
        }

        function open() {
            renderGrid('');
            search.value = '';
            panel.classList.add('show');
            setTimeout(() => search.focus(), 50);
        }
        function close() { panel.classList.remove('show'); }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.classList.contains('show') ? close() : open();
        });

        search.addEventListener('input', function () { renderGrid(this.value); });
        search.addEventListener('click', e => e.stopPropagation());

        grid.addEventListener('click', function (e) {
            const item = e.target.closest('.icon-picker-item');
            if (!item) return;
            input.value = 'bi-' + item.getAttribute('data-icon');
            renderPreview();
            close();
        });

        // Fecha ao clicar fora
        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) close();
        });

        // Permite atualizar a pré-visualização quando o valor é setado por código
        input._iconPickerRender = renderPreview;

        renderPreview();
    }

    /**
     * Define o valor de um icon-picker por id e atualiza a pré-visualização.
     */
    window.setIconPickerValue = function (inputId, value) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.value = value || 'bi-easel';
        if (typeof input._iconPickerRender === 'function') {
            input._iconPickerRender();
        }
    };

    function init() {
        document.querySelectorAll('.icon-picker-input').forEach(createPicker);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Reexpor para reinicializar se necessário
    window.initIconPickers = init;
})();
