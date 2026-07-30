<?php use App\Core\Auth; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar3 me-2"></i>Calendário</h2>
    <?php if (Auth::hasPermission('meetings.create')): ?>
    <a href="/meetings/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nova Reunião
    </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div id="fullcalendar"></div>
    </div>
</div>

<!-- Modal de detalhe de reserva do totem -->
<div class="modal fade" id="calResModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Reserva de Sala</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="calResBody"></div>
        </div>
    </div>
</div>
