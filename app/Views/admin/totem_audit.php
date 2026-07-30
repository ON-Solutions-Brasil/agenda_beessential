<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clock-history me-2"></i>Logs de Ações</h2>
    <a href="/admin/totem" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <strong>Alterações nas reservas</strong>
        <span class="badge bg-light text-muted ms-2"><?= count($entries) ?> registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Quando</th>
                        <th>Reserva</th>
                        <th>Ação</th>
                        <th>Campo</th>
                        <th>De</th>
                        <th>Para</th>
                        <th>Por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma alteração registrada.</td></tr>
                    <?php else: foreach ($entries as $e): ?>
                    <tr>
                        <td class="small text-muted" style="white-space:nowrap">
                            <?= date('d/m/Y H:i', strtotime($e->created_at)) ?>
                        </td>
                        <td class="small">
                            #<?= (int)$e->reservation_id ?>
                            <?php if (!empty($e->customer_name)): ?>
                            <div class="text-muted"><?= View::escape($e->customer_name) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $actionBadge = [
                                'created'   => ['bg-success', 'Criada'],
                                'updated'   => ['bg-warning text-dark', 'Alterada'],
                                'cancelled' => ['bg-danger', 'Cancelada'],
                            ];
                            [$cls, $lbl] = $actionBadge[$e->action] ?? ['bg-secondary', $e->action];
                            ?>
                            <span class="badge <?= $cls ?>"><?= $lbl ?></span>
                        </td>
                        <td class="small"><strong><?= View::escape($labels[$e->field] ?? ($e->field ?? '-')) ?></strong></td>
                        <td class="small text-muted">
                            <?php $old = trim((string)($e->old_value ?? '')); ?>
                            <?= $old !== '' ? View::escape($old) : '<span class="text-muted fst-italic">(vazio)</span>' ?>
                        </td>
                        <td class="small">
                            <i class="bi bi-arrow-right text-muted me-1"></i>
                            <?php $new = trim((string)($e->new_value ?? '')); ?>
                            <?= $new !== '' ? '<strong>' . View::escape($new) . '</strong>' : '<span class="text-muted fst-italic">(vazio)</span>' ?>
                        </td>
                        <td class="small text-muted">
                            <?= View::escape($e->actor ?? '-') ?>
                            <?php if (!empty($e->ip_address)): ?>
                            <div class="text-muted" style="font-size:.75rem"><?= View::escape($e->ip_address) ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
