<?php use App\Core\Auth; use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
    <?php if (Auth::hasPermission('meetings.create')): ?>
    <a href="/meetings/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nova Reunião
    </a>
    <?php endif; ?>
</div>

<!-- Estatísticas -->
<?php if (!empty($stats)): ?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                    <i class="bi bi-people text-primary" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?= $stats['total_users'] ?? 0 ?></h3>
                    <small class="text-muted">Usuários</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                    <i class="bi bi-camera-video text-success" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?= $stats['total_meetings'] ?? 0 ?></h3>
                    <small class="text-muted">Total Reuniões</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
                    <i class="bi bi-calendar-day text-warning" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?= $stats['meetings_today'] ?? 0 ?></h3>
                    <small class="text-muted">Hoje</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                    <i class="bi bi-calendar-week text-info" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?= $stats['meetings_week'] ?? 0 ?></h3>
                    <small class="text-muted">Esta Semana</small>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($totemStats)): ?>
<?php
    $ts = $totemStats;
    $sum = $ts['summary'];
    $totalRes = (int) ($sum->total ?? 0);
    $hoursTotal = round(((int)($sum->seconds ?? 0)) / 3600, 1);
    // Ocupação do dia: horas reservadas hoje vs. capacidade (salas x expediente ~10h)
    $maxProcura = 0;
    foreach ($ts['room_ranking'] as $rk) { $maxProcura = max($maxProcura, (int)$rk->total); }
?>
<div class="d-flex align-items-center mt-4 mb-3 flex-wrap gap-2">
    <i class="bi bi-easel text-dark me-2" style="font-size:1.3rem"></i>
    <h4 class="mb-0">Indicadores do Totem</h4>
    <span class="badge bg-light text-muted">últimos 30 dias</span>
    <?php if (!empty($units)): ?>
    <form method="GET" action="/dashboard" class="ms-auto d-flex align-items-center gap-2">
        <label class="small text-muted mb-0">Unidade:</label>
        <select class="form-select form-select-sm" name="unit" style="width:auto"
                onchange="this.form.submit()">
            <option value="">Todas</option>
            <?php foreach ($units as $u): ?>
            <option value="<?= (int)$u->id ?>" <?= ($selectedUnit === (int)$u->id) ? 'selected' : '' ?>>
                <?= \App\Core\View::escape($u->name) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="p-3 rounded me-3" style="background:rgba(255,193,7,.15)">
                    <i class="bi bi-calendar-check" style="font-size:1.5rem;color:#FFA000"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?= $totalRes ?></h3>
                    <small class="text-muted">Reservas (30 dias)</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="p-3 rounded me-3" style="background:rgba(22,163,74,.12)">
                    <i class="bi bi-clock-history text-success" style="font-size:1.5rem"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?= $hoursTotal ?>h</h3>
                    <small class="text-muted">Horas reservadas</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="p-3 rounded me-3" style="background:rgba(13,110,253,.1)">
                    <i class="bi bi-calendar-day text-primary" style="font-size:1.5rem"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?= (int)($sum->today ?? 0) ?></h3>
                    <small class="text-muted">Reservas hoje</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="p-3 rounded me-3" style="background:rgba(220,38,38,.1)">
                    <i class="bi bi-x-circle text-danger" style="font-size:1.5rem"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?= (int)($sum->cancelled ?? 0) ?></h3>
                    <small class="text-muted">Cancelamentos</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Salas com maior procura / ocupação -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-trophy text-warning me-2"></i>
                <strong>Salas com maior procura</strong>
                <span class="badge bg-light text-muted ms-auto">taxa de ocupação</span>
            </div>
            <div class="card-body">
                <?php if (empty($ts['room_ranking']) || $totalRes === 0): ?>
                <div class="text-center text-muted py-3">Sem reservas no período.</div>
                <?php else: foreach ($ts['room_ranking'] as $rk): ?>
                <?php
                    $total = (int)$rk->total;
                    $horas = round(((int)$rk->seconds) / 3600, 1);
                    // Taxa de ocupação = horas reservadas / horas disponíveis no período
                    $ocupacao = $ts['available_hours'] > 0
                        ? min(100, round($horas / $ts['available_hours'] * 100))
                        : 0;
                    $barColor = $ocupacao >= 70 ? '#16a34a' : ($ocupacao >= 30 ? '#FFC107' : '#f59e0b');
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><strong><?= View::escape($rk->name) ?></strong></span>
                        <span class="text-muted small"><?= $ocupacao ?>% · <?= $total ?> reservas · <?= $horas ?>h</span>
                    </div>
                    <div class="progress" style="height:12px;background:#eef1f8">
                        <div class="progress-bar" role="progressbar"
                             style="width:<?= $ocupacao ?>%;background:<?= $barColor ?>"><?= $ocupacao ?>%</div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
                <div class="text-muted small mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Ocupação = horas reservadas ÷ horas disponíveis (<?= $ts['hours_per_day'] ?>h/dia × <?= $ts['days'] ?> dias).
                </div>
            </div>
        </div>
    </div>

    <!-- Vendedores mais ativos -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-person-badge text-primary me-2"></i>
                <strong>Vendedores mais ativos</strong>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ts['seller_ranking'])): ?>
                <div class="text-center text-muted py-4">Sem dados de vendedores no período.</div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($ts['seller_ranking'] as $i => $sk): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <span class="badge bg-dark me-2"><?= $i + 1 ?></span>
                            <?= View::escape($sk->seller_name) ?>
                        </span>
                        <span class="badge" style="background:#FFC107;color:#111"><?= (int)$sk->total ?> reservas</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Reuniões de Hoje -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-calendar-day text-primary me-2"></i>
                <strong>Reuniões de Hoje</strong>
                <span class="badge bg-primary ms-auto"><?= count($todayMeetings ?? []) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayMeetings)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-calendar-x d-block mb-2" style="font-size: 2rem;"></i>
                    Nenhuma reunião para hoje.
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($todayMeetings as $meeting): ?>
                    <a href="/meetings/<?= $meeting->id ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-circle p-1" style="background-color: <?= View::escape($meeting->color ?? '#3788d8') ?>; width: 10px; height: 10px;"></span>
                                    <strong><?= View::escape($meeting->title) ?></strong><?= !empty($meeting->location) ? ' <span class="text-muted fw-normal small">' . View::escape($meeting->location) . '</span>' : '' ?>
                                </div>
                                <small class="text-muted"><?= substr($meeting->start_time, 0, 5) ?> - <?= substr($meeting->end_time, 0, 5) ?></small>
                            </div>
                            <?php if ($meeting->meet_link): ?>
                            <span class="badge bg-success"><i class="bi bi-camera-video"></i> Meet</span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Próximas Reuniões -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-clock text-success me-2"></i>
                <strong>Próximas Reuniões</strong>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingMeetings)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-calendar-plus d-block mb-2" style="font-size: 2rem;"></i>
                    Nenhuma reunião agendada.
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($upcomingMeetings as $meeting): ?>
                    <a href="/meetings/<?= $meeting->id ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= View::escape($meeting->title) ?></strong><?= !empty($meeting->location) ? ' <span class="text-muted fw-normal small">' . View::escape($meeting->location) . '</span>' : '' ?>
                                <div class="small text-muted">
                                    <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($meeting->meeting_date)) ?>
                                    <i class="bi bi-clock ms-2 me-1"></i><?= substr($meeting->start_time, 0, 5) ?>
                                </div>
                            </div>
                            <span class="badge bg-<?= $meeting->status === 'confirmed' ? 'success' : 'secondary' ?>">
                                <?= $meeting->status === 'confirmed' ? 'Confirmada' : 'Agendada' ?>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
