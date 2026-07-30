<?php use App\Core\View; use App\Core\Auth; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-gear me-2"></i>Configurações do Sistema</h2>
</div>

<form method="POST" action="/settings/save">
    <?= View::csrf() ?>

    <?php
    $groupLabels = [
        'geral' => 'Geral',
        'reunioes' => 'Reuniões',
        'horarios' => 'Horários de Trabalho',
        'google' => 'Google API',
        'totem' => 'Modo Totem',
        'notificacoes' => 'Notificações',
    ];
    ?>

    <?php foreach ($settings as $group => $items): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <strong><?= View::escape($groupLabels[$group] ?? ucfirst($group)) ?></strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($items as $setting): ?>
                <div class="col-md-6">
                    <label for="setting_<?= $setting->key_name ?>" class="form-label">
                        <?= View::escape($setting->label) ?>
                    </label>

                    <?php if ($setting->key_name === 'work_days'): ?>
                    <?php
                        $selectedDays = json_decode($setting->value ?? '[]', true);
                        if (!is_array($selectedDays)) { $selectedDays = []; }
                        $selectedDays = array_map('strval', $selectedDays);
                        $weekDays = [
                            '0' => 'Domingo', '1' => 'Segunda', '2' => 'Terça', '3' => 'Quarta',
                            '4' => 'Quinta', '5' => 'Sexta', '6' => 'Sábado',
                        ];
                    ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($weekDays as $num => $label): ?>
                        <label class="btn btn-sm <?= in_array($num, $selectedDays, true) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            <input type="checkbox" class="d-none" name="settings[work_days][]" value="<?= $num ?>"
                                   <?= in_array($num, $selectedDays, true) ? 'checked' : '' ?>
                                   onchange="this.closest('label').classList.toggle('btn-primary', this.checked); this.closest('label').classList.toggle('btn-outline-secondary', !this.checked);">
                            <?= $label ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif ($setting->type === 'boolean'): ?>
                    <select class="form-select" id="setting_<?= $setting->key_name ?>" name="settings[<?= $setting->key_name ?>]">
                        <option value="1" <?= $setting->value ? 'selected' : '' ?>>Sim</option>
                        <option value="0" <?= !$setting->value ? 'selected' : '' ?>>Não</option>
                    </select>
                    <?php elseif ($setting->type === 'number'): ?>
                    <input type="number" class="form-control" id="setting_<?= $setting->key_name ?>"
                           name="settings[<?= $setting->key_name ?>]" value="<?= View::escape($setting->value ?? '') ?>">
                    <?php else: ?>
                    <input type="text" class="form-control" id="setting_<?= $setting->key_name ?>"
                           name="settings[<?= $setting->key_name ?>]" value="<?= View::escape($setting->value ?? '') ?>">
                    <?php endif; ?>

                    <?php if ($setting->description): ?>
                    <small class="form-text text-muted"><?= View::escape($setting->description) ?></small>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (Auth::hasPermission('settings.edit')): ?>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Salvar Configurações
        </button>
    </div>
    <?php endif; ?>
</form>
