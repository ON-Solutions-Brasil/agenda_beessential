<?php use App\Core\View; ?>
<div class="totem-header">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-easel fs-3"></i>
        <div>
            <div class="totem-title">Reserva de Salas</div>
            <div class="totem-subtitle" id="totemDate"></div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="totem-clock" id="totemClock">--:--</div>
        <a href="/totem/exit" class="btn btn-outline-light btn-sm totem-exit"
           onclick="return confirm('Sair do modo Totem?');">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div>

<div class="totem-main">
    <div class="totem-rooms" id="roomsGrid">
        <div class="text-center text-muted w-100 py-5">
            <div class="spinner-border" role="status"></div>
            <p class="mt-2">Carregando salas...</p>
        </div>
    </div>
</div>

<!-- Painel lateral de detalhes/reserva -->
<div class="totem-panel" id="roomPanel">
    <div class="totem-panel-header">
        <div>
            <div class="totem-panel-title" id="panelRoomName"></div>
            <div class="totem-panel-status" id="panelRoomStatus"></div>
        </div>
        <button type="button" class="btn-close-panel" id="closePanel"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="totem-panel-body">
        <div id="panelNext" class="totem-next"></div>

        <div id="panelDescription" class="totem-room-desc"></div>

        <!-- Prévia dos itens da sala (o que o visitante pode esperar) -->
        <div id="panelItemsSection">
            <div class="totem-section-head">
                <h6 class="totem-section-title mb-0"><i class="bi bi-stars me-1"></i>O que esta sala oferece</h6>
                <button type="button" class="totem-checklist-toggle" id="checklistToggle">
                    <i class="bi bi-check2-square me-1"></i>Checklist
                </button>
            </div>
            <div class="totem-items" id="panelItems"></div>
            <div class="totem-checklist-progress" id="checklistProgress" style="display:none;"></div>
        </div>

        <h6 class="totem-section-title mt-3"><i class="bi bi-clock me-1"></i>Horários</h6>
        <div class="totem-slots" id="panelSlots"></div>

        <!-- Formulário de reserva -->
        <div class="totem-reserve-form" id="reserveForm" style="display:none;">
            <h6 class="totem-section-title mt-3"><i class="bi bi-pencil-square me-1"></i>Nova Reserva</h6>
            <div class="totem-selected-slot" id="selectedSlotLabel"></div>
            <?= View::csrf() ?>
            <input type="hidden" id="fieldRoomId">
            <input type="hidden" id="fieldStartTime">

            <label class="form-label totem-duration-label"><i class="bi bi-stopwatch me-1"></i>Duração</label>
            <div class="totem-durations" id="durationOptions"></div>

            <div class="mb-3">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" id="fieldName" placeholder="Seu nome">
            </div>
            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="tel" class="form-control form-control-lg" id="fieldPhone" placeholder="(00) 00000-0000">
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail <span id="emailRequired" class="text-danger" style="display:none;">*</span></label>
                <input type="email" class="form-control form-control-lg" id="fieldEmail" placeholder="email@exemplo.com">
            </div>
            <div class="totem-form-error" id="formError"></div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary btn-lg flex-fill" id="cancelReserve">Cancelar</button>
                <button type="button" class="btn btn-primary btn-lg flex-fill" id="confirmReserve">
                    <i class="bi bi-check-lg me-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
<div class="totem-overlay" id="panelOverlay"></div>

<!-- Confirmação de sucesso -->
<div class="totem-success" id="successModal">
    <div class="totem-success-card">
        <i class="bi bi-check-circle-fill totem-success-icon"></i>
        <h3>Reserva Confirmada!</h3>
        <p id="successDetails" class="text-muted"></p>
        <button type="button" class="btn btn-primary btn-lg" id="successOk">OK</button>
    </div>
</div>

<script>
    window.TOTEM_CONFIG = <?= json_encode($config) ?>;
</script>
