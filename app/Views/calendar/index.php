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

<!-- Modal de detalhe/edição de reserva do totem -->
<div class="modal fade" id="calResModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Reserva de Sala</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?= \App\Core\View::csrf() ?>
                <input type="hidden" id="calResId">
                <div class="mb-2"><span class="text-muted">Sala:</span> <strong id="calResRoom"></strong></div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small">Data</label>
                        <input type="date" class="form-control form-control-sm" id="calResDate">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Início</label>
                        <input type="time" class="form-control form-control-sm" id="calResStart">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Fim</label>
                        <input type="time" class="form-control form-control-sm" id="calResEnd">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Cliente</label>
                        <input type="text" class="form-control form-control-sm" id="calResName">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Telefone</label>
                        <input type="text" class="form-control form-control-sm" id="calResPhone">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">E-mail</label>
                        <input type="email" class="form-control form-control-sm" id="calResEmail">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Vendedor</label>
                        <select class="form-select form-select-sm" id="calResSeller">
                            <option value="">—</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Interesse</label>
                        <input type="text" class="form-control form-control-sm" id="calResInterest">
                    </div>
                </div>
                <div class="text-danger small mt-2" id="calResError"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="calResDelete">
                        <i class="bi bi-trash me-1"></i>Excluir
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="calResCancel">
                        <i class="bi bi-x-circle me-1"></i>Cancelar reserva
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="calResSave">
                    <i class="bi bi-check-lg me-1"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>
