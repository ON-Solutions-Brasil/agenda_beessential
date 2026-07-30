<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-badge me-2"></i>Gerenciar Roles</h2>
    <a href="/admin/roles/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Novo Role
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Descrição</th>
                        <th>Usuários</th>
                        <th>SuperAdmin</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                    <tr>
                        <td class="fw-medium"><?= View::escape($role->name) ?></td>
                        <td><code><?= View::escape($role->slug) ?></code></td>
                        <td class="small text-muted"><?= View::escape($role->description ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= $role->user_count ?? 0 ?></span></td>
                        <td>
                            <?php if ((int)$role->is_superadmin): ?>
                            <span class="badge bg-warning"><i class="bi bi-star-fill"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="/admin/roles/<?= $role->id ?>/edit" class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if (!(int)$role->is_superadmin): ?>
                                <form method="POST" action="/admin/roles/<?= $role->id ?>/delete" class="d-inline" onsubmit="return confirm('Excluir este role?')">
                                    <?= View::csrf() ?>
                                    <button type="submit" class="btn btn-outline-danger" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
