<?php use App\Core\View; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-list-check me-2"></i>Itens de Demonstração</h2>
        <div class="text-muted">
            <i class="bi <?= View::escape($room->icon) ?> me-1"></i><?= View::escape($room->name) ?>
        </div>
    </div>
    <a href="/admin/totem" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Itens da sala</strong>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#itemModal"
                onclick="prepareItemModal()">
            <i class="bi bi-plus-lg me-1"></i>Novo Item
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
                        <th>Descrição</th>
                        <th>Ativo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">
                        Nenhum item cadastrado. Adicione os itens que esta sala oferece para demonstração.
                    </td></tr>
                    <?php else: foreach ($items as $item): ?>
                    <tr>
                        <td><?= (int) $item->sort_order ?></td>
                        <td><i class="bi <?= View::escape($item->icon) ?> fs-5"></i></td>
                        <td><strong><?= View::escape($item->name) ?></strong></td>
                        <td class="small text-muted"><?= View::escape($item->description ?? '-') ?></td>
                        <td>
                            <?php if ((int)$item->active === 1): ?>
                            <span class="badge bg-success">Sim</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Não</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary"
                                    onclick='editItem(<?= json_encode([
                                        "id" => (int)$item->id,
                                        "name" => $item->name,
                                        "description" => $item->description,
                                        "icon" => $item->icon,
                                        "active" => (int)$item->active,
                                        "sort_order" => (int)$item->sort_order,
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="/admin/totem/items/<?= (int)$item->id ?>/delete"
                                  class="d-inline" onsubmit="return confirm('Excluir este item?');">
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

<!-- Modal de item -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="itemForm" action="/admin/totem/items/store">
                <?= View::csrf() ?>
                <input type="hidden" name="room_id" value="<?= (int)$room->id ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">Novo Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="itemName" required
                                   placeholder="Ex: Projetor 4K">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição (prévia para o visitante)</label>
                            <input type="text" class="form-control" name="description" id="itemDescription"
                                   placeholder="Ex: Demonstração de imagem em alta resolução">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ícone (Bootstrap Icons)</label>
                            <input type="text" class="form-control" name="icon" id="itemIcon"
                                   value="bi-check2-circle" placeholder="bi-check2-circle">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ordem</label>
                            <input type="number" class="form-control" name="sort_order" id="itemSortOrder" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ativo</label>
                            <select class="form-select" name="active" id="itemActive">
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
function prepareItemModal() {
    document.getElementById('itemModalTitle').textContent = 'Novo Item';
    document.getElementById('itemForm').action = '/admin/totem/items/store';
    document.getElementById('itemName').value = '';
    document.getElementById('itemDescription').value = '';
    document.getElementById('itemIcon').value = 'bi-check2-circle';
    document.getElementById('itemSortOrder').value = '0';
    document.getElementById('itemActive').value = '1';
}

function editItem(item) {
    document.getElementById('itemModalTitle').textContent = 'Editar Item';
    document.getElementById('itemForm').action = '/admin/totem/items/' + item.id + '/update';
    document.getElementById('itemName').value = item.name || '';
    document.getElementById('itemDescription').value = item.description || '';
    document.getElementById('itemIcon').value = item.icon || 'bi-check2-circle';
    document.getElementById('itemSortOrder').value = item.sort_order || 0;
    document.getElementById('itemActive').value = String(item.active);
    new bootstrap.Modal(document.getElementById('itemModal')).show();
}
</script>
