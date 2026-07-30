<?php use App\Core\Auth; use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-gear me-2"></i>Usuários</h2>
    <?php if (Auth::hasPermission('users.create')): ?>
    <a href="/users/create" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Novo Usuário
    </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Último Login</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="fw-medium"><?= View::escape($user->name) ?></td>
                        <td><?= View::escape($user->email) ?></td>
                        <td><?= View::escape($user->phone ?: '-') ?></td>
                        <td><span class="badge bg-primary"><?= View::escape($user->role_name ?? '-') ?></span></td>
                        <td>
                            <?php if ((int)$user->active): ?>
                            <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?= $user->last_login_at ? date('d/m/Y H:i', strtotime($user->last_login_at)) : 'Nunca' ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <?php if (Auth::hasPermission('users.edit')): ?>
                                <a href="/users/<?= $user->id ?>/edit" class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (Auth::hasPermission('users.delete') && (int)$user->role_id !== 1): ?>
                                <form method="POST" action="/users/<?= $user->id ?>/delete" class="d-inline" onsubmit="return confirm('Excluir este usuário?')">
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
