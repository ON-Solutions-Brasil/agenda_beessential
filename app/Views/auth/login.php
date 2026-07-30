<?php use App\Core\Session; use App\Core\View; ?>
<div class="login-card card border-0">
    <div class="login-header">
        <i class="bi bi-calendar-check" style="font-size: 3rem;"></i>
        <h3 class="mt-2 mb-0">Agenda Beessential</h3>
        <small class="opacity-75">Sistema de Agendamento de Reuniões</small>
    </div>
    <div class="card-body p-4">
        <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-danger py-2">
            <i class="bi bi-exclamation-triangle me-1"></i><?= Session::getFlash('error') ?>
        </div>
        <?php endif; ?>
        <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success py-2">
            <i class="bi bi-check-circle me-1"></i><?= Session::getFlash('success') ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <?= View::csrf() ?>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Sua senha" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
            </button>
        </form>

        <div class="text-center mt-3">
            <div class="d-flex align-items-center my-3">
                <hr class="flex-grow-1">
                <span class="px-2 text-muted small">ou</span>
                <hr class="flex-grow-1">
            </div>
            <a href="/totem/pin" class="btn btn-outline-primary w-100 py-2">
                <i class="bi bi-easel me-2"></i>Acessar Totem
            </a>
        </div>
    </div>
</div>
