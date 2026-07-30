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
