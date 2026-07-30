<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-gear me-2"></i>Editar Usuário</h2>
    <a href="/users" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="/users/<?= $user->id ?>/update">
            <?= View::csrf() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= View::escape($user->name) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= View::escape($user->email) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= View::escape($user->phone ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="role_id" class="form-label">Role</label>
                    <select class="form-select" id="role_id" name="role_id">
                        <?php foreach ($roles as $role): ?>
                        <option value="<?= $role->id ?>" <?= (int)$user->role_id === (int)$role->id ? 'selected' : '' ?>>
                            <?= View::escape($role->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="active" class="form-label">Status</label>
                    <select class="form-select" id="active" name="active">
                        <option value="1" <?= (int)$user->active === 1 ? 'selected' : '' ?>>Ativo</option>
                        <option value="0" <?= (int)$user->active === 0 ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Nova Senha</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6">
                    <small class="form-text text-muted">Deixe em branco para manter a senha atual</small>
                </div>
            </div>
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Salvar Alterações
                </button>
                <a href="/users" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
