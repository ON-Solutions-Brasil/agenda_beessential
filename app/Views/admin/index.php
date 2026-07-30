<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-shield-lock me-2"></i>Painel Administrativo</h2>
</div>

<!-- Estatísticas -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-4">
            <i class="bi bi-people text-primary" style="font-size:2rem"></i>
            <h3 class="mt-2"><?= $stats['total_users'] ?? 0 ?></h3>
            <small class="text-muted">Usuários</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-4">
            <i class="bi bi-camera-video text-success" style="font-size:2rem"></i>
            <h3 class="mt-2"><?= $stats['total_meetings'] ?? 0 ?></h3>
            <small class="text-muted">Reuniões</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-4">
            <i class="bi bi-person-badge text-warning" style="font-size:2rem"></i>
            <h3 class="mt-2"><?= $stats['total_roles'] ?? 0 ?></h3>
            <small class="text-muted">Roles</small>
        </div>
    </div>
</div>

<!-- Log de atividades recentes -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <strong><i class="bi bi-clock-history me-1"></i>Atividades Recentes</strong>
    </div>
    <div class="card-body p-0">
        <?php if (empty($stats['recent_logs'])): ?>
        <div class="p-4 text-center text-muted">Nenhuma atividade registrada.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Descrição</th>
                        <th>IP</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_logs'] as $log): ?>
                    <tr>
                        <td><?= View::escape($log->user_name ?? 'Sistema') ?></td>
                        <td><span class="badge bg-secondary"><?= View::escape($log->action) ?></span></td>
                        <td class="small"><?= View::escape($log->description ?? '-') ?></td>
                        <td class="small text-muted"><?= View::escape($log->ip_address ?? '-') ?></td>
                        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($log->created_at)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
