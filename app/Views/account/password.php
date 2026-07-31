<?php use App\Core\View; use App\Core\Auth; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-shield-lock me-2"></i>Alterar Senha</h2>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong><?= View::escape(Auth::userName() ?? '') ?></strong>
                <div class="small text-muted"><?= View::escape(Auth::userEmail() ?? '') ?></div>
            </div>
            <div class="card-body">
                <form method="POST" action="/account/password" autocomplete="off">
                    <?= View::csrf() ?>
                    <div class="mb-3">
                        <label class="form-label">Senha atual <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nova senha <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_password" required minlength="6" autocomplete="new-password">
                        <small class="text-muted">Mínimo de 6 caracteres.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar nova senha <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="confirm_password" required minlength="6" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Salvar nova senha
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
