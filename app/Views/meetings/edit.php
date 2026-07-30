<?php use App\Core\View; use App\Core\Auth; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square me-2"></i>Editar Reunião</h2>
    <a href="/meetings" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="/meetings/<?= $meeting->id ?>/update">
            <?= View::csrf() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= View::escape($meeting->title) ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="color" class="form-label">Cor</label>
                    <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="<?= View::escape($meeting->color ?? '#3788d8') ?>">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="scheduled" <?= $meeting->status === 'scheduled' ? 'selected' : '' ?>>Agendada</option>
                        <option value="confirmed" <?= $meeting->status === 'confirmed' ? 'selected' : '' ?>>Confirmada</option>
                        <option value="completed" <?= $meeting->status === 'completed' ? 'selected' : '' ?>>Realizada</option>
                        <option value="cancelled" <?= $meeting->status === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="meeting_date" class="form-label">Data <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="meeting_date" name="meeting_date" value="<?= $meeting->meeting_date ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="start_time" class="form-label">Hora Início <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="start_time" name="start_time" value="<?= substr($meeting->start_time, 0, 5) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="end_time" class="form-label">Hora Fim <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="end_time" name="end_time" value="<?= substr($meeting->end_time, 0, 5) ?>" required>
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Descrição</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= View::escape($meeting->description ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="location" class="form-label">Local</label>
                    <input type="text" class="form-control" id="location" name="location" value="<?= View::escape($meeting->location ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="participants" class="form-label">Participantes</label>
                    <select class="form-select" id="participants" name="participants[]" multiple size="5">
                        <?php foreach ($users as $user): ?>
                            <?php if ((int)$user->id !== (int)$meeting->organizer_id): ?>
                            <option value="<?= $user->id ?>" <?= in_array((int)$user->id, $participantIds ?? []) ? 'selected' : '' ?>>
                                <?= View::escape($user->name) ?> (<?= View::escape($user->email) ?>)
                            </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Segure Ctrl/Cmd para selecionar múltiplos</small>
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Observações</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"><?= View::escape($meeting->notes ?? '') ?></textarea>
                </div>
            </div>

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Salvar Alterações
                </button>
                <a href="/meetings" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
