/**
 * Agenda Beessential - JavaScript principal
 */
document.addEventListener('DOMContentLoaded', function () {

    // ─── Inicializar FullCalendar ─────────────────────────
    initCalendar();

    // ─── Auto-dismiss de alertas ──────────────────────────
    autoDismissAlerts();

    // ─── Marcar nav ativo ─────────────────────────────────
    highlightActiveNav();
});

/**
 * Inicializa o FullCalendar na página do calendário.
 */
function initCalendar() {
    const calendarEl = document.getElementById('fullcalendar');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'pt-br',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia'
        },
        events: function (info, successCallback, failureCallback) {
            fetch('/calendar/events?start=' + info.startStr + '&end=' + info.endStr)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Erro ao carregar eventos:', error);
                    failureCallback(error);
                });
        },
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            const props = info.event.extendedProps;
            // Reserva de sala do totem: abre modal com detalhes
            if (props.tipo) {
                showCalReservation(info.event, props);
                return;
            }
            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },
        eventDidMount: function (info) {
            // Tooltip com detalhes
            const props = info.event.extendedProps;
            let tooltip = info.event.title;
            if (props.tipo) {
                tooltip += '\n' + props.tipo;
                if (props.visitante) tooltip += '\nVisitante: ' + props.visitante;
                if (props.telefone) tooltip += '\nTelefone: ' + props.telefone;
                if (props.vendedor) tooltip += '\nVendedor: ' + props.vendedor;
                if (props.interesse) tooltip += '\nInteresse: ' + props.interesse;
            } else {
                if (props.organizer) tooltip += '\nOrganizador: ' + props.organizer;
                if (props.location) tooltip += '\nLocal: ' + props.location;
            }
            info.el.setAttribute('title', tooltip);
        },
        height: 'auto',
        navLinks: true,
        editable: false,
        selectable: false,
        nowIndicator: true,
        dayMaxEvents: true,
    });

    calendar.render();
    window._kiroCalendar = calendar;
    initCalReservationActions();
}

/**
 * Abre o modal editável com os detalhes de uma reserva de sala (evento do totem).
 * O id do evento vem como "r<ID>".
 */
function showCalReservation(event, props) {
    const modalEl = document.getElementById('calResModal');
    if (!modalEl) return;

    const resId = String(event.id).replace(/^r/, '');
    const errEl = document.getElementById('calResError');
    errEl.textContent = '';

    fetch('/admin/totem/reservations/' + resId)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { errEl.textContent = data.message || 'Erro'; return; }
            const r = data.reservation;
            document.getElementById('calResId').value = r.id;
            document.getElementById('calResRoom').textContent = r.room || props.sala || '';
            document.getElementById('calResDate').value = r.date_iso || '';
            document.getElementById('calResStart').value = r.start || '';
            document.getElementById('calResEnd').value = r.end || '';
            document.getElementById('calResName').value = r.customer_name || '';
            document.getElementById('calResPhone').value = r.customer_phone || '';
            document.getElementById('calResEmail').value = r.customer_email || '';
            document.getElementById('calResInterest').value = r.interest || '';

            // Popula vendedores da unidade
            const sel = document.getElementById('calResSeller');
            sel.innerHTML = '<option value="">—</option>' +
                (data.sellers || []).map(s =>
                    '<option value="' + s.id + '"' + (r.seller_id === s.id ? ' selected' : '') + '>' +
                    escapeHtmlCal(s.name) + '</option>'
                ).join('');
        })
        .catch(() => { errEl.textContent = 'Erro ao carregar a reserva.'; });

    new bootstrap.Modal(modalEl).show();
}

function calGetCsrf() {
    const el = document.querySelector('#calResModal input[name="_csrf_token"]');
    return el ? el.value : '';
}

function calReloadCalendar() {
    if (window._kiroCalendar) window._kiroCalendar.refetchEvents();
}

function initCalReservationActions() {
    const modalEl = document.getElementById('calResModal');
    if (!modalEl) return;
    const errEl = document.getElementById('calResError');
    const idOf = () => document.getElementById('calResId').value;
    const hide = () => bootstrap.Modal.getInstance(modalEl).hide();

    document.getElementById('calResSave').addEventListener('click', function () {
        const body = new URLSearchParams();
        body.append('_csrf_token', calGetCsrf());
        body.append('reservation_date', document.getElementById('calResDate').value);
        body.append('start_time', document.getElementById('calResStart').value);
        body.append('end_time', document.getElementById('calResEnd').value);
        body.append('customer_name', document.getElementById('calResName').value.trim());
        body.append('customer_phone', document.getElementById('calResPhone').value.trim());
        body.append('customer_email', document.getElementById('calResEmail').value.trim());
        body.append('seller_id', document.getElementById('calResSeller').value);
        body.append('interest', document.getElementById('calResInterest').value.trim());

        this.disabled = true;
        fetch('/admin/totem/reservations/' + idOf() + '/update', { method: 'POST', body: body })
            .then(r => r.json())
            .then(data => {
                if (data.success) { hide(); calReloadCalendar(); }
                else { errEl.textContent = data.message || 'Erro ao salvar.'; }
            })
            .catch(() => { errEl.textContent = 'Erro de conexão.'; })
            .finally(() => { this.disabled = false; });
    });

    document.getElementById('calResCancel').addEventListener('click', function () {
        if (!confirm('Cancelar esta reserva? O horário será liberado.')) return;
        const body = new URLSearchParams();
        body.append('_csrf_token', calGetCsrf());
        fetch('/admin/totem/reservations/' + idOf() + '/cancel', { method: 'POST', body: body })
            .then(r => r.json())
            .then(data => { if (data.success) { hide(); calReloadCalendar(); } else { errEl.textContent = data.message; } })
            .catch(() => { errEl.textContent = 'Erro de conexão.'; });
    });

    document.getElementById('calResDelete').addEventListener('click', function () {
        if (!confirm('Excluir permanentemente esta reserva? Esta ação não pode ser desfeita.')) return;
        const body = new URLSearchParams();
        body.append('_csrf_token', calGetCsrf());
        fetch('/admin/totem/reservations/' + idOf() + '/delete', { method: 'POST', body: body })
            .then(r => r.json())
            .then(data => { if (data.success) { hide(); calReloadCalendar(); } else { errEl.textContent = data.message; } })
            .catch(() => { errEl.textContent = 'Erro de conexão.'; });
    });
}

function formatTime(d) {
    return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
}

function escapeHtmlCal(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Remove alertas automaticamente após 5 segundos.
 */
function autoDismissAlerts() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });
}

/**
 * Destaca o item de navegação ativo com base na URL.
 */
function highlightActiveNav() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    navLinks.forEach(function (link) {
        const href = link.getAttribute('href');
        if (!href) return;

        if (currentPath === href || (href !== '/' && currentPath.startsWith(href))) {
            link.classList.add('active');
        }
    });
}
