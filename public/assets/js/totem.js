/**
 * Modo Totem - lógica de autoatendimento.
 * Duas telas: teclado de PIN e dashboard de salas com atualização em tempo real.
 */
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('pinForm')) {
        initPinKeypad();
    }
    if (document.getElementById('roomsGrid')) {
        initTotemDashboard();
    }
});

/* ─── Teclado de PIN ─────────────────────────────────── */
function initPinKeypad() {
    const input = document.getElementById('pinInput');
    const submit = document.getElementById('pinSubmit');
    let pin = '';

    function render() {
        input.value = pin;
        submit.disabled = pin.length !== 4;
    }

    document.querySelectorAll('.totem-key').forEach(function (key) {
        key.addEventListener('click', function () {
            const digit = key.getAttribute('data-key');
            const action = key.getAttribute('data-action');

            if (action === 'clear') {
                pin = '';
            } else if (action === 'back') {
                pin = pin.slice(0, -1);
            } else if (digit !== null && pin.length < 4) {
                pin += digit;
            }
            render();

            // Auto-submit ao completar 4 dígitos
            if (pin.length === 4) {
                setTimeout(function () { document.getElementById('pinForm').submit(); }, 200);
            }
        });
    });
    render();
}

/* ─── Dashboard do Totem ─────────────────────────────── */
function initTotemDashboard() {
    const config = window.TOTEM_CONFIG || {};
    const grid = document.getElementById('roomsGrid');
    const csrfToken = document.querySelector('input[name="_csrf_token"]').value;

    let currentRoom = null;
    let selectedSlot = null;
    let selectedDuration = null;
    let selectedMaxMinutes = 0;
    let checklistMode = false;
    let checkedItems = new Set();
    let refreshMs = (config.refresh_seconds || 15) * 1000;
    let roomsData = [];
    let timer = null;

    const slotMin = config.slot_minutes || 30;
    const minDur = config.min_duration || slotMin;
    const maxDur = config.max_duration || slotMin;
    const defaultDur = config.default_duration || slotMin;

    const statusLabels = {
        available: '<i class="bi bi-check-circle"></i> Disponível',
        occupied: '<i class="bi bi-x-circle"></i> Ocupada',
        soon: '<i class="bi bi-clock"></i> Reservada em breve'
    };
    const statusText = { available: 'Disponível', occupied: 'Ocupada', soon: 'Reservada em breve' };

    // Relógio
    function updateClock() {
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('totemClock').textContent = hh + ':' + mm;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Busca dados das salas
    function loadRooms() {
        fetch('/totem/rooms', { headers: { 'Accept': 'application/json' } })
            .then(function (r) {
                return r.text().then(function (text) {
                    if (!r.ok) {
                        throw new Error('HTTP ' + r.status + ': ' + text.slice(0, 300));
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Resposta inválida do servidor: ' + text.slice(0, 300));
                    }
                });
            })
            .then(data => {
                roomsData = data.rooms || [];
                refreshMs = data.refresh_ms || refreshMs;
                renderDate(data.date);
                renderRooms(roomsData);
                // Se o painel estiver aberto, atualiza também
                if (currentRoom) {
                    const updated = roomsData.find(r => r.id === currentRoom.id);
                    if (updated) refreshPanel(updated);
                }
            })
            .catch(err => {
                console.error('Erro ao carregar salas:', err);
                showLoadError(err.message);
            })
            .finally(scheduleNext);
    }

    function showLoadError(msg) {
        grid.innerHTML =
            '<div class="totem-load-error">' +
                '<i class="bi bi-exclamation-triangle"></i>' +
                '<div class="totem-load-error-title">Não foi possível carregar as salas</div>' +
                '<div class="totem-load-error-msg">' + escapeHtml(msg || 'Erro desconhecido') + '</div>' +
                '<div class="totem-load-error-hint">Nova tentativa automática em instantes...</div>' +
            '</div>';
    }

    function scheduleNext() {
        clearTimeout(timer);
        timer = setTimeout(loadRooms, refreshMs);
    }

    function renderDate(dateStr) {
        const el = document.getElementById('totemDate');
        const d = new Date(dateStr + 'T00:00:00');
        el.textContent = d.toLocaleDateString('pt-BR', {
            weekday: 'long', day: '2-digit', month: 'long'
        });
    }

    function renderRooms(rooms) {
        if (!rooms.length) {
            grid.innerHTML = '<div class="text-center text-muted w-100 py-5">Nenhuma sala disponível.</div>';
            return;
        }
        grid.innerHTML = rooms.map(room => {
            let info = '';
            if (room.status === 'occupied' && room.current) {
                info = 'Ocupada até ' + room.current.end;
            } else if (room.status === 'soon' && room.next) {
                info = 'Reserva às ' + room.next.start;
            } else if (room.next) {
                info = 'Próxima às ' + room.next.start;
            } else {
                info = 'Livre pelo restante do dia';
            }
            const cap = room.capacity ? '<div class="totem-room-capacity"><i class="bi bi-people me-1"></i>' + room.capacity + ' pessoas</div>' : '<div class="totem-room-capacity">&nbsp;</div>';
            return '<div class="totem-room-card status-' + room.status + '" data-room-id="' + room.id + '">' +
                '<i class="bi ' + room.icon + ' totem-room-icon"></i>' +
                '<div class="totem-room-name">' + escapeHtml(room.name) + '</div>' +
                cap +
                '<span class="totem-status-badge status-' + room.status + '">' + statusLabels[room.status] + '</span>' +
                '<div class="totem-room-info">' + info + '</div>' +
                '</div>';
        }).join('');

        grid.querySelectorAll('.totem-room-card').forEach(card => {
            card.addEventListener('click', function () {
                const id = parseInt(card.getAttribute('data-room-id'), 10);
                const room = roomsData.find(r => r.id === id);
                if (room) openPanel(room);
            });
        });
    }

    /* ─── Painel lateral ─── */
    const panel = document.getElementById('roomPanel');
    const overlay = document.getElementById('panelOverlay');

    function openPanel(room) {
        currentRoom = room;
        selectedSlot = null;
        checklistMode = false;
        checkedItems.clear();
        updateChecklistToggle();
        hideReserveForm();
        refreshPanel(room);
        panel.classList.add('show');
        overlay.classList.add('show');
    }

    function closePanel() {
        panel.classList.remove('show');
        overlay.classList.remove('show');
        currentRoom = null;
        selectedSlot = null;
        checklistMode = false;
    }

    function updateChecklistToggle() {
        const btn = document.getElementById('checklistToggle');
        if (!btn) return;
        btn.classList.toggle('active', checklistMode);
        btn.innerHTML = checklistMode
            ? '<i class="bi bi-eye me-1"></i>Prévia'
            : '<i class="bi bi-check2-square me-1"></i>Checklist';
    }

    function refreshPanel(room) {
        currentRoom = room;
        document.getElementById('panelRoomName').textContent = room.name;
        document.getElementById('panelRoomStatus').innerHTML = statusLabels[room.status];

        const nextEl = document.getElementById('panelNext');
        if (room.status === 'occupied' && room.current) {
            nextEl.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Em uso até ' + room.current.end;
        } else if (room.next) {
            nextEl.innerHTML = '<i class="bi bi-calendar-event me-1"></i>Próxima reserva às ' + room.next.start;
        } else {
            nextEl.innerHTML = '';
        }

        // Descrição (prévia geral da sala)
        const descEl = document.getElementById('panelDescription');
        descEl.textContent = room.description || '';
        descEl.style.display = room.description ? 'block' : 'none';

        renderItems(room);
        renderSlots(room);
    }

    // Renderiza os itens da sala. Em modo checklist ficam marcáveis pelo vendedor.
    function renderItems(room) {
        const section = document.getElementById('panelItemsSection');
        const container = document.getElementById('panelItems');
        const items = room.items || [];

        if (!items.length) {
            section.style.display = 'none';
            return;
        }
        section.style.display = 'block';

        container.innerHTML = items.map((it) => {
            const desc = it.description
                ? '<div class="totem-item-desc">' + escapeHtml(it.description) + '</div>' : '';
            const isChecked = checklistMode && checkedItems.has(it.name);
            const checkIcon = isChecked ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            const check = checklistMode
                ? '<span class="totem-item-check"><i class="' + checkIcon + '"></i></span>' : '';
            const checkedCls = isChecked ? ' checked' : '';
            return '<div class="totem-item' + (checklistMode ? ' checkable' : '') + checkedCls + '" ' +
                'data-name="' + escapeHtml(it.name) + '">' +
                check +
                '<i class="bi ' + (it.icon || 'bi-check2-circle') + ' totem-item-icon"></i>' +
                '<div class="totem-item-info">' +
                    '<div class="totem-item-name">' + escapeHtml(it.name) + '</div>' + desc +
                '</div>' +
            '</div>';
        }).join('');

        if (checklistMode) {
            container.querySelectorAll('.totem-item.checkable').forEach(el => {
                el.addEventListener('click', function () {
                    const name = el.getAttribute('data-name');
                    const done = el.classList.toggle('checked');
                    if (done) { checkedItems.add(name); } else { checkedItems.delete(name); }
                    el.querySelector('.totem-item-check i').className =
                        done ? 'bi bi-check-circle-fill' : 'bi bi-circle';
                    updateChecklistProgress();
                });
            });
        }
        updateChecklistProgress();
    }

    function updateChecklistProgress() {
        const progressEl = document.getElementById('checklistProgress');
        if (!checklistMode) {
            progressEl.style.display = 'none';
            return;
        }
        const total = document.querySelectorAll('#panelItems .totem-item').length;
        const done = document.querySelectorAll('#panelItems .totem-item.checked').length;
        progressEl.style.display = 'block';
        const pct = total ? Math.round((done / total) * 100) : 0;
        progressEl.innerHTML =
            '<div class="totem-progress-text">' + done + ' de ' + total + ' itens demonstrados</div>' +
            '<div class="totem-progress-bar"><span style="width:' + pct + '%"></span></div>';
    }

    function renderSlots(room) {
        const container = document.getElementById('panelSlots');
        if (!room.slots || !room.slots.length) {
            container.innerHTML = '<div class="text-muted">Sem horários configurados.</div>';
            return;
        }
        container.innerHTML = room.slots.map(slot => {
            let cls = 'totem-slot ';
            if (slot.occupied) cls += 'occupied';
            else if (!slot.available) cls += 'past';
            else cls += 'available';
            const sel = (selectedSlot === slot.start) ? ' selected' : '';
            return '<div class="' + cls + sel + '" data-start="' + slot.start + '" ' +
                'data-max="' + (slot.max_minutes || 0) + '">' + slot.start + '</div>';
        }).join('');

        container.querySelectorAll('.totem-slot.available').forEach(el => {
            el.addEventListener('click', function () {
                selectedSlot = el.getAttribute('data-start');
                selectedMaxMinutes = parseInt(el.getAttribute('data-max'), 10) || 0;
                container.querySelectorAll('.totem-slot').forEach(s => s.classList.remove('selected'));
                el.classList.add('selected');
                showReserveForm(room, selectedSlot, selectedMaxMinutes);
            });
        });
    }

    // Gera as opções de duração possíveis para o slot escolhido,
    // limitadas pelo tempo contíguo livre e pelo máximo configurado.
    function renderDurations(maxMinutes) {
        const container = document.getElementById('durationOptions');
        const options = [];
        for (let d = minDur; d <= Math.min(maxMinutes, maxDur); d += slotMin) {
            options.push(d);
        }

        if (!options.length) {
            container.innerHTML = '<div class="totem-duration-empty">Sem tempo suficiente a partir deste horário.</div>';
            selectedDuration = null;
            return;
        }

        // Duração pré-selecionada: padrão se couber, senão a maior disponível
        selectedDuration = options.includes(defaultDur) ? defaultDur : options[options.length - 1];

        container.innerHTML = options.map(d => {
            const label = formatDuration(d);
            const active = (d === selectedDuration) ? ' active' : '';
            return '<button type="button" class="totem-duration-chip' + active + '" data-duration="' + d + '">' +
                label + '</button>';
        }).join('');

        container.querySelectorAll('.totem-duration-chip').forEach(chip => {
            chip.addEventListener('click', function () {
                selectedDuration = parseInt(chip.getAttribute('data-duration'), 10);
                container.querySelectorAll('.totem-duration-chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                updateSlotSummary();
            });
        });
    }

    function formatDuration(min) {
        if (min < 60) return min + ' min';
        const h = Math.floor(min / 60);
        const m = min % 60;
        return m === 0 ? h + 'h' : h + 'h' + String(m).padStart(2, '0');
    }

    function addMinutes(hhmm, minutes) {
        const [h, m] = hhmm.split(':').map(Number);
        const total = h * 60 + m + minutes;
        return String(Math.floor(total / 60)).padStart(2, '0') + ':' + String(total % 60).padStart(2, '0');
    }

    function updateSlotSummary() {
        if (!selectedSlot || !selectedDuration || !currentRoom) return;
        const end = addMinutes(selectedSlot, selectedDuration);
        document.getElementById('selectedSlotLabel').innerHTML =
            '<i class="bi bi-door-open me-1"></i>' + escapeHtml(currentRoom.name) +
            '<span class="totem-slot-time">' + selectedSlot + ' – ' + end + '</span>';
    }

    /* ─── Formulário de reserva ─── */
    function showReserveForm(room, start, maxMinutes) {
        document.getElementById('fieldRoomId').value = room.id;
        document.getElementById('fieldStartTime').value = start;
        document.getElementById('formError').textContent = '';
        document.getElementById('emailRequired').style.display = config.require_email ? 'inline' : 'none';
        renderDurations(maxMinutes);
        updateSlotSummary();
        document.getElementById('reserveForm').style.display = 'block';
        document.getElementById('reserveForm').scrollIntoView({ behavior: 'smooth' });
    }

    function hideReserveForm() {
        const form = document.getElementById('reserveForm');
        if (form) {
            form.style.display = 'none';
            document.getElementById('fieldName').value = '';
            document.getElementById('fieldPhone').value = '';
            document.getElementById('fieldEmail').value = '';
            document.getElementById('formError').textContent = '';
        }
    }

    document.getElementById('closePanel').addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);

    document.getElementById('checklistToggle').addEventListener('click', function () {
        checklistMode = !checklistMode;
        updateChecklistToggle();
        if (currentRoom) renderItems(currentRoom);
    });
    document.getElementById('cancelReserve').addEventListener('click', function () {
        selectedSlot = null;
        selectedDuration = null;
        document.querySelectorAll('.totem-slot').forEach(s => s.classList.remove('selected'));
        hideReserveForm();
    });

    document.getElementById('confirmReserve').addEventListener('click', function () {
        const btn = this;
        const errEl = document.getElementById('formError');
        const name = document.getElementById('fieldName').value.trim();
        const phone = document.getElementById('fieldPhone').value.trim();
        const email = document.getElementById('fieldEmail').value.trim();

        if (!selectedDuration) { errEl.textContent = 'Selecione a duração.'; return; }
        if (!name) { errEl.textContent = 'Informe o nome.'; return; }
        if (config.require_email && !email) { errEl.textContent = 'Informe o e-mail.'; return; }

        const body = new URLSearchParams();
        body.append('_csrf_token', csrfToken);
        body.append('room_id', document.getElementById('fieldRoomId').value);
        body.append('start_time', document.getElementById('fieldStartTime').value);
        body.append('duration', selectedDuration);
        body.append('customer_name', name);
        body.append('customer_phone', phone);
        body.append('customer_email', email);

        btn.disabled = true;
        fetch('/totem/reserve', { method: 'POST', body: body })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    closePanel();
                    showSuccess(data.reservation);
                    loadRooms();
                } else {
                    errEl.textContent = data.message || 'Não foi possível reservar.';
                    loadRooms();
                }
            })
            .catch(() => { errEl.textContent = 'Erro de conexão. Tente novamente.'; })
            .finally(() => { btn.disabled = false; });
    });

    /* ─── Sucesso ─── */
    function showSuccess(r) {
        const modal = document.getElementById('successModal');
        document.getElementById('successDetails').textContent =
            r.room + ' • ' + r.start + ' às ' + r.end;
        modal.classList.add('show');
    }
    document.getElementById('successOk').addEventListener('click', function () {
        document.getElementById('successModal').classList.remove('show');
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Início
    loadRooms();
}
