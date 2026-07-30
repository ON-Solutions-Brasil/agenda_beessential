<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-envelope-paper me-2"></i>Logs de Envio</h2>
    <a href="/admin/totem" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Canal</th>
                        <th>Destinatário</th>
                        <th>Assunto / Evento</th>
                        <th>Status</th>
                        <th>Detalhe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum envio registrado ainda.</td></tr>
                    <?php else: foreach ($logs as $log): ?>
                    <tr>
                        <td class="small text-muted" style="white-space:nowrap">
                            <?= date('d/m/Y H:i', strtotime($log->created_at)) ?>
                        </td>
                        <td>
                            <?php if ($log->channel === 'email'): ?>
                            <span class="badge bg-info"><i class="bi bi-envelope me-1"></i>E-mail</span>
                            <?php else: ?>
                            <span class="badge bg-dark"><i class="bi bi-link-45deg me-1"></i>Webhook</span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= View::escape($log->recipient ?? '-') ?></td>
                        <td class="small"><?= View::escape($log->subject ?? '-') ?></td>
                        <td>
                            <?php if ($log->status === 'success'): ?>
                            <span class="badge bg-success">Enviado</span>
                            <?php elseif ($log->status === 'skipped'): ?>
                            <span class="badge bg-secondary">Ignorado</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Falhou</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= View::escape($log->error ?? '-') ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
