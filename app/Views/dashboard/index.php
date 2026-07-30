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
                                    <strong><?= View::escape($meeting->title) ?></strong>
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
                                <strong><?= View::escape($meeting->title) ?></strong>
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
