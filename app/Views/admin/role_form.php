<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-person-badge me-2"></i>
        <?= $role ? 'Editar Role' : 'Novo Role' ?>
    </h2>
    <a href="/admin/roles" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= $role ? "/admin/roles/{$role->id}/update" : '/admin/roles/store' ?>">
            <?= View::csrf() ?>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?= View::escape($role->name ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="slug" name="slug"
                           value="<?= View::escape($role->slug ?? '') ?>" required
                           pattern="[a-z0-9_-]+" title="Apenas letras minúsculas, números, - e _">
                </div>
                <div class="col-md-4">
                    <label for="description" class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="description" name="description"
                           value="<?= View::escape($role->description ?? '') ?>">
                </div>
            </div>

            <h5 class="mb-3">Permissões</h5>

            <?php
            $groupLabels = [
                'reunioes' => 'Reuniões',
                'calendario' => 'Calendário',
                'usuarios' => 'Usuários',
                'configuracoes' => 'Configurações',
                'admin' => 'Administração',
                'geral' => 'Geral',
            ];
            ?>

            <div class="row g-3">
                <?php foreach ($permissions as $group => $perms): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-header bg-light py-2">
                            <strong><?= View::escape($groupLabels[$group] ?? ucfirst($group)) ?></strong>
                        </div>
                        <div class="card-body">
                            <?php foreach ($perms as $perm): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                       value="<?= $perm->id ?>" id="perm_<?= $perm->id ?>"
                                       <?= in_array((int)$perm->id, $rolePermissionIds ?? []) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="perm_<?= $perm->id ?>">
                                    <?= View::escape($perm->name) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= $role ? 'Salvar Alterações' : 'Criar Role' ?>
                </button>
                <a href="/admin/roles" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
