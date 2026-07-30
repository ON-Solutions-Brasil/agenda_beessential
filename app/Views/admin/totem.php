<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-easel me-2"></i>Configuração do Modo Totem</h2>
    <a href="/totem" target="_blank" class="btn btn-outline-primary">
        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir Totem
    </a>
</div>

<!-- Configurações gerais -->
<form method="POST" action="/admin/totem/save" class="mb-4">
    <?= View::csrf() ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Acesso e Funcionamento</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Modo Totem</label>
                    <select class="form-select" name="totem_enabled">
                        <option value="1" <?= $config['totem_enabled'] ? 'selected' : '' ?>>Ativado</option>
                        <option value="0" <?= !$config['totem_enabled'] ? 'selected' : '' ?>>Desativado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">PIN de Acesso (4 dígitos)</label>
                    <input type="text" class="form-control" name="totem_pin" maxlength="4" pattern="\d{4}"
                           inputmode="numeric" value="<?= View::escape((string)($config['totem_pin'] ?? '')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Atualização Automática (segundos)</label>
                    <input type="number" class="form-control" name="totem_refresh_seconds" min="5" max="120"
                           value="<?= View::escape((string)($config['totem_refresh_seconds'] ?? 15)) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Início do Funcionamento</label>
                    <input type="time" class="form-control" name="totem_open_time"
                           value="<?= View::escape((string)($config['totem_open_time'] ?? '08:00')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fim do Funcionamento</label>
                    <input type="time" class="form-control" name="totem_close_time"
                           value="<?= View::escape((string)($config['totem_close_time'] ?? '18:00')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Intervalo das Janelas (min)</label>
                    <input type="number" class="form-control" name="totem_slot_minutes" min="5" max="240"
                           value="<?= View::escape((string)($config['totem_slot_minutes'] ?? 30)) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Antecedência Mínima (min)</label>
                    <input type="number" class="form-control" name="totem_advance_minutes" min="0"
                           value="<?= View::escape((string)($config['totem_advance_minutes'] ?? 0)) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tempo Padrão de Reserva (min)</label>
                    <input type="number" class="form-control" name="totem_default_duration" min="5"
                           value="<?= View::escape((string)($config['totem_default_duration'] ?? 30)) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tempo Mínimo de Reserva (min)</label>
                    <input type="number" class="form-control" name="totem_min_duration" min="5"
                           value="<?= View::escape((string)($config['totem_min_duration'] ?? 30)) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tempo Máximo de Reserva (min)</label>
                    <input type="number" class="form-control" name="totem_max_duration" min="5"
                           value="<?= View::escape((string)($config['totem_max_duration'] ?? 120)) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">E-mail Obrigatório</label>
                    <select class="form-select" name="totem_require_email">
                        <option value="1" <?= $config['totem_require_email'] ? 'selected' : '' ?>>Sim</option>
                        <option value="0" <?= !$config['totem_require_email'] ? 'selected' : '' ?>>Não</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar Configurações</button>
        </div>
    </div>
</form>

<!-- Gestão de salas -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Salas</strong>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#roomModal"
                onclick="prepareRoomModal()">
            <i class="bi bi-plus-lg me-1"></i>Nova Sala
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ordem</th>
                        <th>Ícone</th>
                        <th>Nome</th>
                        <th>Capacidade</th>
                        <th>Ativa</th>
                        <th>No Totem</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma sala cadastrada.</td></tr>
                    <?php else: foreach ($rooms as $room): ?>
                    <tr>
                        <td><?= (int) $room->sort_order ?></td>
                        <td><i class="bi <?= View::escape($room->icon) ?> fs-5"></i></td>
                        <td>
                            <strong><?= View::escape($room->name) ?></strong>
                            <?php if ($room->description): ?>
                            <div class="small text-muted"><?= View::escape($room->description) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= $room->capacity ? (int)$room->capacity . ' pessoas' : '-' ?></td>
                        <td>
                            <?php if ((int)$room->active === 1): ?>
                            <span class="badge bg-success">Sim</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Não</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int)$room->show_in_totem === 1): ?>
                            <span class="badge bg-primary">Sim</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Não</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="/admin/totem/rooms/<?= (int)$room->id ?>/items"
                               class="btn btn-sm btn-outline-secondary" title="Itens de demonstração">
                                <i class="bi bi-list-check"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-primary"
                                    onclick='editRoom(<?= json_encode([
                                        "id" => (int)$room->id,
                                        "name" => $room->name,
                                        "description" => $room->description,
                                        "icon" => $room->icon,
                                        "capacity" => $room->capacity,
                                        "active" => (int)$room->active,
                                        "show_in_totem" => (int)$room->show_in_totem,
                                        "sort_order" => (int)$room->sort_order,
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="/admin/totem/rooms/<?= (int)$room->id ?>/delete"
                                  class="d-inline" onsubmit="return confirm('Excluir esta sala e suas reservas?');">
                                <?= View::csrf() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de sala -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="roomForm" action="/admin/totem/rooms/store">
                <?= View::csrf() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="roomModalTitle">Nova Sala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="roomName" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <input type="text" class="form-control" name="description" id="roomDescription">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ícone (Bootstrap Icons)</label>
                            <input type="text" class="form-control" name="icon" id="roomIcon" value="bi-easel"
                                   placeholder="bi-easel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacidade</label>
                            <input type="number" class="form-control" name="capacity" id="roomCapacity" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ordem</label>
                            <input type="number" class="form-control" name="sort_order" id="roomSortOrder" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ativa</label>
                            <select class="form-select" name="active" id="roomActive">
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Exibir no Totem</label>
                            <select class="form-select" name="show_in_totem" id="roomShowInTotem">
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function prepareRoomModal() {
    document.getElementById('roomModalTitle').textContent = 'Nova Sala';
    document.getElementById('roomForm').action = '/admin/totem/rooms/store';
    document.getElementById('roomName').value = '';
    document.getElementById('roomDescription').value = '';
    document.getElementById('roomIcon').value = 'bi-easel';
    document.getElementById('roomCapacity').value = '';
    document.getElementById('roomSortOrder').value = '0';
    document.getElementById('roomActive').value = '1';
    document.getElementById('roomShowInTotem').value = '1';
}

function editRoom(room) {
    document.getElementById('roomModalTitle').textContent = 'Editar Sala';
    document.getElementById('roomForm').action = '/admin/totem/rooms/' + room.id + '/update';
    document.getElementById('roomName').value = room.name || '';
    document.getElementById('roomDescription').value = room.description || '';
    document.getElementById('roomIcon').value = room.icon || 'bi-easel';
    document.getElementById('roomCapacity').value = room.capacity || '';
    document.getElementById('roomSortOrder').value = room.sort_order || 0;
    document.getElementById('roomActive').value = String(room.active);
    document.getElementById('roomShowInTotem').value = String(room.show_in_totem);
    new bootstrap.Modal(document.getElementById('roomModal')).show();
}
</script>
