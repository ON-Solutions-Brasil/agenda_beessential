<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-lines-fill me-2"></i>Clientes</h2>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="/clients" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Buscar por nome, telefone ou e-mail</label>
                <input type="text" class="form-control" name="q" value="<?= View::escape($search) ?>"
                       placeholder="Digite para filtrar...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Unidade</label>
                <select class="form-select" name="unit">
                    <option value="">Todas</option>
                    <?php foreach ($units as $u): ?>
                    <option value="<?= (int)$u->id ?>" <?= ($selectedUnit === (int)$u->id) ? 'selected' : '' ?>>
                        <?= View::escape($u->name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Buscar</button>
            </div>
            <?php if ($search !== '' || $selectedUnit !== null): ?>
            <div class="col-md-2">
                <a href="/clients" class="btn btn-outline-secondary w-100">Limpar</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center">
        <strong>Histórico de atendimentos</strong>
        <span class="badge bg-light text-muted ms-2"><?= count($clients) ?> registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Unidade</th>
                        <th>Sala reservada</th>
                        <th>Quem atendeu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clients)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhum registro encontrado.</td></tr>
                    <?php else: foreach ($clients as $c): ?>
                    <tr>
                        <td class="small text-muted" style="white-space:nowrap">
                            <?= date('d/m/Y', strtotime($c->reservation_date)) ?>
                            <div><?= substr($c->start_time,0,5) ?> – <?= substr($c->end_time,0,5) ?></div>
                        </td>
                        <td>
                            <strong><?= View::escape($c->customer_name) ?></strong>
                            <?php if ($c->status === 'cancelled'): ?>
                            <span class="badge bg-secondary ms-1">Cancelada</span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= View::escape($c->customer_phone ?? '-') ?></td>
                        <td class="small"><?= View::escape($c->customer_email ?? '-') ?></td>
                        <td class="small text-muted"><?= View::escape($c->unit_name ?? '-') ?></td>
                        <td><span class="badge" style="background:#111;color:#FFC107"><?= View::escape($c->room_name) ?></span></td>
                        <td class="small text-muted"><?= View::escape($c->seller_name ?? '-') ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
