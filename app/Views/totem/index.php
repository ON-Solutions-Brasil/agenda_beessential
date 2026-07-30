<?php use App\Core\View; ?>
<div class="totem-header">
    <div class="totem-brand">
        <?php if (!empty($logo)): ?>
        <img src="<?= View::escape($logo) ?>" alt="Logo" class="totem-logo">
        <?php else: ?>
        <i class="bi bi-easel totem-logo-icon"></i>
        <?php endif; ?>
        <div class="totem-title">Sala de Reservas</div>
        <div class="totem-subtitle" id="totemDate"></div>
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
                <label class="form-label"><i class="bi bi-person-badge me-1"></i>Vendedor responsável</label>
                <select class="form-select form-select-lg" id="fieldSeller">
                    <option value="">Selecione o vendedor</option>
                    <?php foreach ($sellers as $s): ?>
                    <option value="<?= (int)$s->id ?>"><?= View::escape($s->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Nome do visitante <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" id="fieldName" placeholder="Nome do visitante">
            </div>
            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="tel" class="form-control form-control-lg" id="fieldPhone" placeholder="(00) 00000-0000">
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail <span id="emailRequired" class="text-danger" style="display:none;">*</span></label>
                <input type="email" class="form-control form-control-lg" id="fieldEmail" placeholder="email@exemplo.com">
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-chat-left-text me-1"></i>Interesse do visitante</label>
                <textarea class="form-control form-control-lg" id="fieldInterest" rows="2"
                          placeholder="O que o cliente quer ver? Ex: automação de iluminação, home theater..."></textarea>
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

<!-- Teclado virtual (touch) -->
<div class="totem-vk" id="virtualKeyboard">
    <div class="totem-vk-bar">
        <span class="totem-vk-preview" id="vkPreview"></span>
        <button type="button" class="totem-vk-close" id="vkClose"><i class="bi bi-chevron-down"></i></button>
    </div>
    <div class="totem-vk-keys" id="vkKeys"></div>
</div>

<!-- Modal de edição de reserva (protegido por PIN) -->
<div class="totem-modal" id="editModal">
    <div class="totem-modal-card">
        <div class="totem-modal-header">
            <span id="editModalTitle"><i class="bi bi-lock me-1"></i>Reserva</span>
            <button type="button" class="btn-close-panel" id="closeEdit"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="totem-modal-body">
            <?= View::csrf() ?>
            <input type="hidden" id="editReservationId">

            <!-- Etapa 1: PIN -->
            <div id="editPinStep">
                <p class="text-muted mb-2">Digite o PIN para ver e editar esta reserva.</p>
                <input type="password" class="form-control form-control-lg text-center" id="editPin"
                       inputmode="numeric" maxlength="4" placeholder="••••">
                <div class="totem-form-error mt-2" id="editPinError"></div>
                <button type="button" class="btn btn-primary btn-lg w-100 mt-2" id="editPinSubmit">
                    <i class="bi bi-unlock me-1"></i>Desbloquear
                </button>
            </div>

            <!-- Etapa 2: dados -->
            <div id="editDataStep" style="display:none;">
                <div class="totem-selected-slot" id="editSlotLabel"></div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person-badge me-1"></i>Vendedor</label>
                    <select class="form-select form-select-lg" id="editSeller">
                        <option value="">Selecione o vendedor</option>
                        <?php foreach ($sellers as $s): ?>
                        <option value="<?= (int)$s->id ?>"><?= View::escape($s->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nome do visitante <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg" id="editName">
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefone</label>
                    <input type="tel" class="form-control form-control-lg" id="editPhone">
                </div>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control form-control-lg" id="editEmail">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-chat-left-text me-1"></i>Interesse do visitante</label>
                    <textarea class="form-control form-control-lg" id="editInterest" rows="2"></textarea>
                </div>
                <div class="totem-form-error" id="editError"></div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-danger btn-lg" id="editCancelReservation">
                        <i class="bi bi-trash me-1"></i>Cancelar reserva
                    </button>
                    <button type="button" class="btn btn-primary btn-lg flex-fill" id="editSave">
                        <i class="bi bi-check-lg me-1"></i>Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
