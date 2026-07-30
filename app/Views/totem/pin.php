<?php use App\Core\Session; use App\Core\View; ?>
<div class="totem-pin-wrapper">
    <div class="totem-pin-card">
        <div class="text-center mb-4">
            <i class="bi bi-easel totem-pin-logo"></i>
            <h2 class="mt-2 mb-1">Totem de Reservas</h2>
            <p class="text-muted mb-0">Digite o PIN de acesso</p>
        </div>

        <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-danger text-center py-2"><?= Session::getFlash('error') ?></div>
        <?php endif; ?>

        <form method="POST" action="/totem/pin" id="pinForm">
            <?= View::csrf() ?>
            <input type="password" name="pin" id="pinInput" class="totem-pin-display"
                   inputmode="numeric" maxlength="4" readonly value="" autocomplete="off">

            <div class="totem-keypad">
                <?php foreach ([1,2,3,4,5,6,7,8,9] as $n): ?>
                <button type="button" class="totem-key" data-key="<?= $n ?>"><?= $n ?></button>
                <?php endforeach; ?>
                <button type="button" class="totem-key totem-key-action" data-action="clear">
                    <i class="bi bi-x-lg"></i>
                </button>
                <button type="button" class="totem-key" data-key="0">0</button>
                <button type="button" class="totem-key totem-key-action" data-action="back">
                    <i class="bi bi-backspace"></i>
                </button>
            </div>

            <button type="submit" class="btn btn-primary w-100 totem-btn-lg mt-4" id="pinSubmit" disabled>
                <i class="bi bi-unlock me-2"></i>Entrar
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="/login" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Voltar ao login</a>
        </div>
    </div>
</div>
