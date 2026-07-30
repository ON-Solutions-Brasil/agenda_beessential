<?php use App\Core\View; use App\Core\Auth; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-camera-video me-2"></i>Detalhes da Reunião</h2>
    <div class="d-flex gap-2">
        <?php if (Auth::hasPermission('meetings.edit')): ?>
        <a href="/meetings/<?= $meeting->id ?>/edit" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <?php endif; ?>
        <a href="/meetings" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="rounded-circle d-inline-block" style="width:14px;height:14px;background:<?= View::escape($meeting->color ?? '#3788d8') ?>"></span>
                    <h4 class="mb-0"><?= View::escape($meeting->title) ?></h4>
                </div>

                <?php
                $statusMap = [
                    'scheduled' => ['Agendada', 'secondary'],
                    'confirmed' => ['Confirmada', 'success'],
                    'cancelled' => ['Cancelada', 'danger'],
                    'completed' => ['Realizada', 'info'],
                ];
                $s = $statusMap[$meeting->status] ?? ['Desconhecido', 'secondary'];
                ?>
                <span class="badge bg-<?= $s[1] ?> mb-3"><?= $s[0] ?></span>

                <?php if ($meeting->description): ?>
                <div class="mb-3">
                    <h6 class="text-muted">Descrição</h6>
                    <p><?= nl2br(View::escape($meeting->description)) ?></p>
                </div>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-sm-4">
                        <h6 class="text-muted"><i class="bi bi-calendar3 me-1"></i>Data</h6>
                        <p class="fw-medium"><?= date('d/m/Y', strtotime($meeting->meeting_date)) ?></p>
                    </div>
                    <div class="col-sm-4">
                        <h6 class="text-muted"><i class="bi bi-clock me-1"></i>Horário</h6>
                        <p class="fw-medium"><?= substr($meeting->start_time, 0, 5) ?> - <?= substr($meeting->end_time, 0, 5) ?></p>
                    </div>
                    <div class="col-sm-4">
                        <h6 class="text-muted"><i class="bi bi-geo-alt me-1"></i>Local</h6>
                        <p class="fw-medium"><?= View::escape($meeting->location ?: 'Não definido') ?></p>
                    </div>
                </div>

                <?php if ($meeting->meet_link): ?>
                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="text-muted"><i class="bi bi-camera-video me-1"></i>Link do Google Meet</h6>
                    <a href="<?= View::escape($meeting->meet_link) ?>" target="_blank" class="btn btn-success">
                        <i class="bi bi-camera-video me-1"></i>Entrar na Reunião
                    </a>
                    <small class="d-block mt-1 text-muted"><?= View::escape($meeting->meet_link) ?></small>
                </div>
                <?php endif; ?>

                <?php if ($meeting->notes): ?>
                <div class="mt-3">
                    <h6 class="text-muted">Observações</h6>
                    <p><?= nl2br(View::escape($meeting->notes)) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong><i class="bi bi-people me-1"></i>Participantes</strong>
            </div>
            <div class="card-body p-0">
                <?php if (empty($participants)): ?>
                <div class="p-3 text-center text-muted">Sem participantes</div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($participants as $p): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= View::escape($p->name) ?></span>
                        <span class="badge bg-<?= $p->participation_status === 'accepted' ? 'success' : ($p->participation_status === 'declined' ? 'danger' : 'secondary') ?>">
                            <?= $p->participation_status === 'accepted' ? 'Aceito' : ($p->participation_status === 'declined' ? 'Recusado' : 'Pendente') ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
