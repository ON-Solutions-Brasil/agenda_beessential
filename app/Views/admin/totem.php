<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-easel me-2"></i>Configuração do Modo Totem</h2>
    <div class="d-flex gap-2">
        <a href="/admin/totem/reservations" class="btn btn-outline-secondary">
            <i class="bi bi-calendar-check me-1"></i>Agendamentos
        </a>
        <a href="/admin/totem/audit" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i>Logs de Ações
        </a>
        <a href="/admin/totem/logs" class="btn btn-outline-secondary">
            <i class="bi bi-envelope-paper me-1"></i>Logs de Envio
        </a>
        <a href="/totem" target="_blank" class="btn btn-outline-primary">
            <i class="bi bi-box-arrow-up-right me-1"></i>Abrir Totem
        </a>
    </div>
</div>

<!-- Configurações gerais -->
<form method="POST" action="/admin/totem/save" class="mb-4" enctype="multipart/form-data">
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

                <div class="col-md-8">
                    <label class="form-label">Logo do Totem (PNG)</label>
                    <div class="d-flex align-items-center gap-3">
                        <?php if (!empty($config['totem_logo'])): ?>
                        <img src="<?= View::escape((string)$config['totem_logo']) ?>" alt="Logo"
                             style="height:42px;background:#111;padding:4px;border-radius:6px;">
                        <?php endif; ?>
                        <input type="file" class="form-control" name="totem_logo" accept="image/png">
                    </div>
                    <small class="text-muted">Substitui o ícone no topo do totem. Apenas PNG, até 4 MB.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">PIN padrão (4 dígitos)</label>
                    <input type="text" class="form-control" name="totem_pin" maxlength="4" pattern="\d{4}"
                           inputmode="numeric" value="<?= View::escape((string)($config['totem_pin'] ?? '')) ?>" required>
                    <small class="text-muted">O acesso ao totem usa o PIN de cada <strong>unidade</strong> (abaixo).</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Atualização Automática (segundos)</label>
                    <input type="number" class="form-control" name="totem_refresh_seconds" min="5" max="120"
                           value="<?= View::escape((string)($config['totem_refresh_seconds'] ?? 15)) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Início (padrão)</label>
                    <input type="time" class="form-control" name="totem_open_time"
                           value="<?= View::escape((string)($config['totem_open_time'] ?? '08:00')) ?>">
                    <small class="text-muted">Cada unidade tem seu horário.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fim (padrão)</label>
                    <input type="time" class="form-control" name="totem_close_time"
                           value="<?= View::escape((string)($config['totem_close_time'] ?? '18:00')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Intervalo das Janelas (min)</label>
                    <input type="number" class="form-control" name="totem_slot_minutes" min="5" max="240"
                           value="<?= View::escape((string)($config['totem_slot_minutes'] ?? 30)) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Intervalo entre Reservas (min)</label>
                    <input type="number" class="form-control" name="totem_buffer_minutes" min="0" max="120"
                           value="<?= View::escape((string)($config['totem_buffer_minutes'] ?? 0)) ?>">
                    <small class="text-muted">Folga p/ limpeza e deslocamento</small>
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

    <!-- Notificações: SMTP + Webhook -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Notificações (E-mail e Webhook)</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Envio de E-mail</label>
                    <select class="form-select" name="smtp_enabled">
                        <option value="1" <?= $config['smtp_enabled'] ? 'selected' : '' ?>>Ativado</option>
                        <option value="0" <?= !$config['smtp_enabled'] ? 'selected' : '' ?>>Desativado</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Servidor SMTP</label>
                    <input type="text" class="form-control" name="smtp_host"
                           value="<?= View::escape((string)($config['smtp_host'] ?? '')) ?>" placeholder="smtp.gmail.com">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Porta</label>
                    <input type="number" class="form-control" name="smtp_port"
                           value="<?= View::escape((string)($config['smtp_port'] ?? 587)) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Criptografia</label>
                    <select class="form-select" name="smtp_encryption">
                        <?php $enc = (string)($config['smtp_encryption'] ?? 'tls'); ?>
                        <option value="tls" <?= $enc==='tls'?'selected':'' ?>>TLS</option>
                        <option value="ssl" <?= $enc==='ssl'?'selected':'' ?>>SSL</option>
                        <option value="" <?= $enc===''?'selected':'' ?>>Nenhuma</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Usuário SMTP</label>
                    <input type="text" class="form-control" name="smtp_username"
                           value="<?= View::escape((string)($config['smtp_username'] ?? '')) ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Senha SMTP</label>
                    <input type="password" class="form-control" name="smtp_password"
                           placeholder="<?= !empty($config['smtp_password']) ? '•••••••• (mantém a atual)' : '' ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail Remetente</label>
                    <input type="email" class="form-control" name="smtp_from_email"
                           value="<?= View::escape((string)($config['smtp_from_email'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nome Remetente</label>
                    <input type="text" class="form-control" name="smtp_from_name"
                           value="<?= View::escape((string)($config['smtp_from_name'] ?? '')) ?>">
                </div>

                <div class="col-12"><hr class="my-1"></div>
                <div class="col-md-3">
                    <label class="form-label">Webhook</label>
                    <select class="form-select" name="webhook_enabled">
                        <option value="1" <?= $config['webhook_enabled'] ? 'selected' : '' ?>>Ativado</option>
                        <option value="0" <?= !$config['webhook_enabled'] ? 'selected' : '' ?>>Desativado</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label">URL do Webhook</label>
                    <input type="url" class="form-control" name="webhook_url"
                           value="<?= View::escape((string)($config['webhook_url'] ?? '')) ?>"
                           placeholder="https://seu-endpoint.com/webhook">
                    <small class="text-muted">Recebe um POST JSON com os dados da reserva (vendedor + visitante).</small>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar Configurações</button>
        </div>
    </div>
</form>

<!-- Vendedores -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Vendedores</strong>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#sellerModal"
                onclick="prepareSellerModal()">
            <i class="bi bi-plus-lg me-1"></i>Novo Vendedor
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ordem</th><th>Nome</th><th>Unidade</th><th>E-mail</th><th>Telefone</th><th>Ativo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sellers)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhum vendedor cadastrado.</td></tr>
                    <?php else: foreach ($sellers as $seller): ?>
                    <tr>
                        <td><?= (int)$seller->sort_order ?></td>
                        <td><strong><?= View::escape($seller->name) ?></strong></td>
                        <td class="small"><span class="badge bg-light text-dark"><?= View::escape($seller->unit_name ?? '-') ?></span></td>
                        <td class="small text-muted"><?= View::escape($seller->email ?? '-') ?></td>
                        <td class="small text-muted"><?= View::escape($seller->phone ?? '-') ?></td>
                        <td>
                            <?php if ((int)$seller->active === 1): ?>
                            <span class="badge bg-success">Sim</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Não</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary"
                                    onclick='editSeller(<?= json_encode([
                                        "id" => (int)$seller->id,
                                        "unit_id" => (int)$seller->unit_id,
                                        "name" => $seller->name,
                                        "email" => $seller->email,
                                        "phone" => $seller->phone,
                                        "active" => (int)$seller->active,
                                        "sort_order" => (int)$seller->sort_order,
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="/admin/totem/sellers/<?= (int)$seller->id ?>/delete"
                                  class="d-inline" onsubmit="return confirm('Excluir este vendedor?');">
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

<!-- Gestão de unidades -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Unidades (Totens)</strong>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#unitModal"
                onclick="prepareUnitModal()">
            <i class="bi bi-plus-lg me-1"></i>Nova Unidade
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ordem</th><th>Nome</th><th>Localização</th><th>PIN</th>
                        <th>Funcionamento</th><th>Ativa</th><th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($units)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma unidade cadastrada.</td></tr>
                    <?php else: foreach ($units as $unit): ?>
                    <tr>
                        <td><?= (int)$unit->sort_order ?></td>
                        <td><strong><?= View::escape($unit->name) ?></strong></td>
                        <td class="small text-muted"><?= View::escape($unit->location ?? '-') ?></td>
                        <td><span class="badge" style="background:#111;color:#FFC107;letter-spacing:2px"><?= View::escape($unit->pin) ?></span></td>
                        <td class="small"><?= substr($unit->open_time,0,5) ?> – <?= substr($unit->close_time,0,5) ?></td>
                        <td>
                            <?php if ((int)$unit->active === 1): ?>
                            <span class="badge bg-success">Sim</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Não</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary"
                                    onclick='editUnit(<?= json_encode([
                                        "id" => (int)$unit->id,
                                        "name" => $unit->name,
                                        "location" => $unit->location,
                                        "pin" => $unit->pin,
                                        "open_time" => substr($unit->open_time,0,5),
                                        "close_time" => substr($unit->close_time,0,5),
                                        "active" => (int)$unit->active,
                                        "sort_order" => (int)$unit->sort_order,
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="/admin/totem/units/<?= (int)$unit->id ?>/delete"
                                  class="d-inline" onsubmit="return confirm('Excluir esta unidade e todas as suas salas?');">
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
    <div class="card-footer bg-white">
        <!-- Clonar salas entre unidades (usa salas existentes como padrão) -->
        <form method="POST" action="/admin/totem/units/clone-rooms" class="row g-2 align-items-end"
              onsubmit="return confirm('Copiar todas as salas da unidade de origem para a de destino?');">
            <?= View::csrf() ?>
            <div class="col-md-4">
                <label class="form-label small">Copiar salas de</label>
                <select class="form-select form-select-sm" name="from_unit" required>
                    <option value="">Origem...</option>
                    <?php foreach ($units as $u): ?>
                    <option value="<?= (int)$u->id ?>"><?= View::escape($u->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">para</label>
                <select class="form-select form-select-sm" name="to_unit" required>
                    <option value="">Destino...</option>
                    <?php foreach ($units as $u): ?>
                    <option value="<?= (int)$u->id ?>"><?= View::escape($u->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-files me-1"></i>Copiar salas</button>
            </div>
        </form>
    </div>
</div>

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
                        <th>Unidade</th>
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
                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma sala cadastrada.</td></tr>
                    <?php else: foreach ($rooms as $room): ?>
                    <tr>
                        <td class="small"><span class="badge bg-light text-dark"><?= View::escape($room->unit_name ?? '-') ?></span></td>
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
                                        "unit_id" => (int)$room->unit_id,
                                        "name" => $room->name,
                                        "description" => $room->description,
                                        "icon" => $room->icon,
                                        "image_path" => $room->image_path ?? null,
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
            <form method="POST" id="roomForm" action="/admin/totem/rooms/store" enctype="multipart/form-data">
                <?= View::csrf() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="roomModalTitle">Nova Sala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Unidade <span class="text-danger">*</span></label>
                            <select class="form-select" name="unit_id" id="roomUnit" required>
                                <?php foreach ($units as $u): ?>
                                <option value="<?= (int)$u->id ?>"><?= View::escape($u->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="roomName" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <input type="text" class="form-control" name="description" id="roomDescription">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Imagem da Sala (PNG)</label>
                            <input type="file" class="form-control" name="image" id="roomImage" accept="image/png">
                            <small class="text-muted">Se enviada, substitui o ícone no card. PNG até 4 MB.</small>
                            <div class="form-check mt-2 d-none" id="removeImageWrap">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="roomRemoveImage">
                                <label class="form-check-label" for="roomRemoveImage">Remover imagem atual</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ícone (usado quando não há imagem)</label>
                            <input type="text" class="icon-picker-input" name="icon" id="roomIcon" value="bi-easel">
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

<!-- Modal de vendedor -->
<div class="modal fade" id="sellerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="sellerForm" action="/admin/totem/sellers/store">
                <?= View::csrf() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="sellerModalTitle">Novo Vendedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Unidade <span class="text-danger">*</span></label>
                            <select class="form-select" name="unit_id" id="sellerUnit" required>
                                <?php foreach ($units as $u): ?>
                                <option value="<?= (int)$u->id ?>"><?= View::escape($u->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="sellerName" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">E-mail (recebe notificação)</label>
                            <input type="email" class="form-control" name="email" id="sellerEmail">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" name="phone" id="sellerPhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ordem</label>
                            <input type="number" class="form-control" name="sort_order" id="sellerSortOrder" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ativo</label>
                            <select class="form-select" name="active" id="sellerActive">
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

<!-- Modal de unidade -->
<div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="unitForm" action="/admin/totem/units/store">
                <?= View::csrf() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="unitModalTitle">Nova Unidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="unitName" required
                                   placeholder="Ex: Unidade Centro">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">PIN (4 dígitos) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="pin" id="unitPin" maxlength="4"
                                   pattern="\d{4}" inputmode="numeric" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Localização</label>
                            <input type="text" class="form-control" name="location" id="unitLocation"
                                   placeholder="Endereço ou referência">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Abertura</label>
                            <input type="time" class="form-control" name="open_time" id="unitOpen" value="08:00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fechamento</label>
                            <input type="time" class="form-control" name="close_time" id="unitClose" value="18:00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ativa</label>
                            <select class="form-select" name="active" id="unitActive">
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ordem</label>
                            <input type="number" class="form-control" name="sort_order" id="unitSortOrder" value="0">
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
function prepareUnitModal() {
    document.getElementById('unitModalTitle').textContent = 'Nova Unidade';
    document.getElementById('unitForm').action = '/admin/totem/units/store';
    document.getElementById('unitName').value = '';
    document.getElementById('unitPin').value = '';
    document.getElementById('unitLocation').value = '';
    document.getElementById('unitOpen').value = '08:00';
    document.getElementById('unitClose').value = '18:00';
    document.getElementById('unitActive').value = '1';
    document.getElementById('unitSortOrder').value = '0';
}

function editUnit(unit) {
    document.getElementById('unitModalTitle').textContent = 'Editar Unidade';
    document.getElementById('unitForm').action = '/admin/totem/units/' + unit.id + '/update';
    document.getElementById('unitName').value = unit.name || '';
    document.getElementById('unitPin').value = unit.pin || '';
    document.getElementById('unitLocation').value = unit.location || '';
    document.getElementById('unitOpen').value = unit.open_time || '08:00';
    document.getElementById('unitClose').value = unit.close_time || '18:00';
    document.getElementById('unitActive').value = String(unit.active);
    document.getElementById('unitSortOrder').value = unit.sort_order || 0;
    new bootstrap.Modal(document.getElementById('unitModal')).show();
}

function prepareRoomModal() {
    document.getElementById('roomModalTitle').textContent = 'Nova Sala';
    document.getElementById('roomForm').action = '/admin/totem/rooms/store';
    document.getElementById('roomName').value = '';
    document.getElementById('roomDescription').value = '';
    setIconPickerValue('roomIcon', 'bi-easel');
    document.getElementById('roomCapacity').value = '';
    document.getElementById('roomSortOrder').value = '0';
    document.getElementById('roomActive').value = '1';
    document.getElementById('roomShowInTotem').value = '1';
    document.getElementById('roomImage').value = '';
    document.getElementById('roomRemoveImage').checked = false;
    document.getElementById('removeImageWrap').classList.add('d-none');
}

function editRoom(room) {
    document.getElementById('roomModalTitle').textContent = 'Editar Sala';
    document.getElementById('roomForm').action = '/admin/totem/rooms/' + room.id + '/update';
    if (room.unit_id) document.getElementById('roomUnit').value = String(room.unit_id);
    document.getElementById('roomName').value = room.name || '';
    document.getElementById('roomDescription').value = room.description || '';
    setIconPickerValue('roomIcon', room.icon || 'bi-easel');
    document.getElementById('roomCapacity').value = room.capacity || '';
    document.getElementById('roomSortOrder').value = room.sort_order || 0;
    document.getElementById('roomActive').value = String(room.active);
    document.getElementById('roomShowInTotem').value = String(room.show_in_totem);
    document.getElementById('roomImage').value = '';
    document.getElementById('roomRemoveImage').checked = false;
    document.getElementById('removeImageWrap').classList.toggle('d-none', !room.image_path);
    new bootstrap.Modal(document.getElementById('roomModal')).show();
}

function prepareSellerModal() {
    document.getElementById('sellerModalTitle').textContent = 'Novo Vendedor';
    document.getElementById('sellerForm').action = '/admin/totem/sellers/store';
    document.getElementById('sellerName').value = '';
    document.getElementById('sellerEmail').value = '';
    document.getElementById('sellerPhone').value = '';
    document.getElementById('sellerSortOrder').value = '0';
    document.getElementById('sellerActive').value = '1';
}

function editSeller(seller) {
    document.getElementById('sellerModalTitle').textContent = 'Editar Vendedor';
    document.getElementById('sellerForm').action = '/admin/totem/sellers/' + seller.id + '/update';
    if (seller.unit_id) document.getElementById('sellerUnit').value = String(seller.unit_id);
    document.getElementById('sellerName').value = seller.name || '';
    document.getElementById('sellerEmail').value = seller.email || '';
    document.getElementById('sellerPhone').value = seller.phone || '';
    document.getElementById('sellerSortOrder').value = seller.sort_order || 0;
    document.getElementById('sellerActive').value = String(seller.active);
    new bootstrap.Modal(document.getElementById('sellerModal')).show();
}
</script>
