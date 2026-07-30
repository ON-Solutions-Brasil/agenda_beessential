<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-check me-2"></i>Agendamentos</h2>
    <a href="/admin/totem" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<?php if (!empty($units)): ?>
<form method="GET" action="/admin/totem/reservations" class="mb-3 d-flex align-items-center gap-2">
    <label class="small text-muted mb-0">Unidade:</label>
    <select class="form-select form-select-sm" name="unit" style="width:auto" onchange="this.form.submit()">
        <option value="">Todas</option>
        <?php foreach ($units as $u): ?>
        <option value="<?= (int)$u->id ?>" <?= ($selectedUnit === (int)$u->id) ? 'selected' : '' ?>>
            <?= View::escape($u->name) ?>
        </option>
        <?php endforeach; ?>
    </select>
</form>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Unidade</th>
                        <th>Sala</th>
                        <th>Visitante</th>
                        <th>Vendedor</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhum agendamento registrado.</td></tr>
                    <?php else: foreach ($reservations as $r): ?>
                    <tr class="totem-res-row" style="cursor:pointer"
                        onclick="showReservation(<?= (int)$r->id ?>)">
                        <td class="small"><?= date('d/m/Y', strtotime($r->reservation_date)) ?></td>
                        <td class="small"><?= substr($r->start_time,0,5) ?> – <?= substr($r->end_time,0,5) ?></td>
                        <td class="small text-muted"><?= View::escape($r->unit_name ?? '-') ?></td>
                        <td><strong><?= View::escape($r->room_name) ?></strong></td>
                        <td class="small"><?= View::escape($r->customer_name) ?></td>
                        <td class="small text-muted"><?= View::escape($r->seller_name ?? '-') ?></td>
                        <td>
                            <?php
                            $badges = [
                                'reserved' => ['bg-primary', 'Reservada'],
                                'in_progress' => ['bg-warning text-dark', 'Em andamento'],
                                'completed' => ['bg-success', 'Concluída'],
                                'cancelled' => ['bg-secondary', 'Cancelada'],
                            ];
                            [$cls, $lbl] = $badges[$r->status] ?? ['bg-secondary', $r->status];
                            ?>
                            <span class="badge <?= $cls ?>"><?= $lbl ?></span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();showReservation(<?= (int)$r->id ?>)">
                                <i class="bi bi-eye me-1"></i>Ver
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de detalhe -->
<div class="modal fade" id="resModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Detalhe do Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resModalBody">
                <div class="text-center text-muted py-4">
                    <div class="spinner-border"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showReservation(id) {
    const modal = new bootstrap.Modal(document.getElementById('resModal'));
    const body = document.getElementById('resModalBody');
    body.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border"></div></div>';
    modal.show();

    fetch('/admin/totem/reservations/' + id)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { body.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Erro') + '</div>'; return; }
            const r = data.reservation;
            const row = (label, val) => val ? '<tr><td class="text-muted" style="width:150px">' + label + '</td><td><strong>' + val + '</strong></td></tr>' : '';
            body.innerHTML =
                '<table class="table table-sm">' +
                row('Sala', r.room) +
                row('Data', r.date) +
                row('Horário', r.start + ' – ' + r.end) +
                row('Visitante', r.customer_name) +
                row('Telefone', r.customer_phone) +
                row('E-mail', r.customer_email) +
                row('Vendedor', r.seller_name) +
                row('Tel. vendedor', r.seller_phone) +
                row('Interesse', r.interest) +
                row('Origem', r.source) +
                row('Criado em', r.created_at) +
                '</table>';
        })
        .catch(() => { body.innerHTML = '<div class="alert alert-danger">Erro de conexão.</div>'; });
}
</script>
