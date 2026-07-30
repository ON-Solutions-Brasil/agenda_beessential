<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-key me-2"></i>Gerenciar Permissões</h2>
</div>

<form method="POST" action="/admin/permissions/sync">
    <?= View::csrf() ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Permissão</th>
                            <?php foreach ($roles as $role): ?>
                            <th class="text-center">
                                <?= View::escape($role->name) ?>
                                <?php if ((int)$role->is_superadmin): ?>
                                <br><small class="text-warning">(todas)</small>
                                <?php endif; ?>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permissions as $group => $perms): ?>
                        <tr class="table-secondary">
                            <td colspan="<?= count($roles) + 1 ?>">
                                <strong><?= View::escape(ucfirst($group)) ?></strong>
                            </td>
                        </tr>
                        <?php foreach ($perms as $perm): ?>
                        <tr>
                            <td><?= View::escape($perm->name) ?> <code class="small"><?= $perm->slug ?></code></td>
                            <?php foreach ($roles as $role): ?>
                            <td class="text-center">
                                <?php if ((int)$role->is_superadmin): ?>
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <?php else: ?>
                                <input type="checkbox"
                                       name="role_permissions[<?= $role->id ?>][]"
                                       value="<?= $perm->id ?>"
                                       <?= in_array((int)$perm->id, $rolePermissions[$role->id] ?? []) ? 'checked' : '' ?>>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Salvar Permissões
        </button>
    </div>
</form>
