<?php use App\Core\View; use App\Core\Session; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-envelope-paper me-2"></i>Logs de Envio</h2>
    <a href="/admin/totem" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<?php if (Session::hasFlash('success')): ?>
<div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= Session::getFlash('success') ?></div>
<?php endif; ?>
<?php if (Session::hasFlash('error')): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= Session::getFlash('error') ?></div>
<?php endif; ?>

<!-- Teste rápido de SMTP -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><strong>Testar Envio de E-mail (SMTP)</strong></div>
    <div class="card-body">
        <form method="POST" action="/admin/totem/logs/test" class="row g-2 align-items-end">
            <?= View::csrf() ?>
            <div class="col-md-6">
                <label class="form-label">Enviar e-mail de teste para:</label>
                <input type="email" class="form-control" name="test_email" placeholder="seu@email.com" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-send me-1"></i>Enviar Teste
                </button>
            </div>
            <div class="col-12">
                <small class="text-muted">Usa as configurações SMTP salvas em Modo Totem → Notificações. O resultado é registrado abaixo.</small>
            </div>
        </form>
    </div>
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
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhum envio registrado ainda.</td></tr>
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
                        <td class="text-end">
                            <?php if (!empty($log->reservation_id) || $log->channel === 'email'): ?>
                            <form method="POST" action="/admin/totem/logs/<?= (int)$log->id ?>/resend" class="d-inline">
                                <?= View::csrf() ?>
                                <button class="btn btn-sm btn-outline-primary" title="Reenviar">
                                    <i class="bi bi-arrow-repeat me-1"></i>Reenviar
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
