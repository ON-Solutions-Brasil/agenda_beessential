<?php use App\Core\Auth; use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people me-2"></i>Reuniões</h2>
    <?php if (Auth::hasPermission('meetings.create')): ?>
    <a href="/meetings/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nova Reunião
    </a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($meetings)): ?>
        <div class="p-5 text-center text-muted">
            <i class="bi bi-camera-video-off d-block mb-3" style="font-size: 3rem;"></i>
            <p>Nenhuma reunião encontrada.</p>
            <?php if (Auth::hasPermission('meetings.create')): ?>
            <a href="/meetings/create" class="btn btn-outline-primary">Agendar Primeira Reunião</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Organizador</th>
                        <th>Status</th>
                        <th>Meet</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($meetings as $meeting): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:<?= View::escape($meeting->color ?? '#3788d8') ?>"></span>
                                <a href="/meetings/<?= $meeting->id ?>" class="text-decoration-none fw-medium">
                                    <?= View::escape($meeting->title) ?>
                                </a>
                            </div>
                        </td>
                        <td><?= date('d/m/Y', strtotime($meeting->meeting_date)) ?></td>
                        <td><?= substr($meeting->start_time, 0, 5) ?> - <?= substr($meeting->end_time, 0, 5) ?></td>
                        <td><?= View::escape($meeting->organizer_name ?? '-') ?></td>
                        <td>
                            <?php
                            $statusMap = [
                                'scheduled' => ['Agendada', 'secondary'],
                                'confirmed' => ['Confirmada', 'success'],
                                'cancelled' => ['Cancelada', 'danger'],
                                'completed' => ['Realizada', 'info'],
                            ];
                            $s = $statusMap[$meeting->status] ?? ['Desconhecido', 'secondary'];
                            ?>
                            <span class="badge bg-<?= $s[1] ?>"><?= $s[0] ?></span>
                        </td>
                        <td>
                            <?php if ($meeting->meet_link): ?>
                            <a href="<?= View::escape($meeting->meet_link) ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Abrir Meet">
                                <i class="bi bi-camera-video"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <?php if (Auth::hasPermission('meetings.edit') && $meeting->status !== 'cancelled'): ?>
                                <a href="/meetings/<?= $meeting->id ?>/edit" class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (Auth::hasPermission('meetings.cancel') && $meeting->status === 'scheduled'): ?>
                                <form method="POST" action="/meetings/<?= $meeting->id ?>/cancel" class="d-inline" onsubmit="return confirm('Cancelar esta reunião?')">
                                    <?= View::csrf() ?>
                                    <button type="submit" class="btn btn-outline-warning" title="Cancelar">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if (Auth::hasPermission('meetings.delete')): ?>
                                <form method="POST" action="/meetings/<?= $meeting->id ?>/delete" class="d-inline" onsubmit="return confirm('Excluir permanentemente?')">
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
        <?php endif; ?>
    </div>
</div>
