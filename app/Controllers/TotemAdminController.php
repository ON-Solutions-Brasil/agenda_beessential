<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Setting;
use App\Models\Room;
use App\Models\RoomItem;
use App\Models\Seller;
use App\Models\Unit;
use App\Models\NotificationLog;
use App\Models\ActivityLog;

/**
 * Administração do Modo Totem.
 * Protegido por login/superadmin. Configura PIN, salas, horários e janelas.
 */
class TotemAdminController extends Controller
{
    private Setting $settingModel;
    private Room $roomModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
        $this->roomModel = new Room();
    }

    /**
     * Painel de configuração do totem.
     */
    public function index(): void
    {
        $this->requireSuperAdmin();

        $keys = [
            'totem_enabled', 'totem_pin', 'totem_open_time', 'totem_close_time',
            'totem_slot_minutes', 'totem_buffer_minutes', 'totem_default_duration',
            'totem_min_duration', 'totem_max_duration', 'totem_advance_minutes',
            'totem_require_email', 'totem_refresh_seconds', 'totem_logo',
            'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_encryption',
            'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name',
            'webhook_enabled', 'webhook_url',
        ];

        $config = [];
        foreach ($keys as $key) {
            $config[$key] = $this->settingModel->getValue($key);
        }

        $sellerModel = new Seller();
        $unitModel = new Unit();

        $this->view('admin/totem', [
            'config'  => $config,
            'rooms'   => $this->roomModel->allWithUnit(),
            'sellers' => $sellerModel->allOrdered(),
            'units'   => $unitModel->allOrdered(),
        ]);
    }

    /**
     * Cria uma unidade.
     */
    public function storeUnit(): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $name = trim((string) $this->input('name', ''));
        $pin  = trim((string) $this->input('pin', ''));

        if ($name === '') {
            Session::flash('error', 'O nome da unidade é obrigatório.');
            $this->redirect('/admin/totem');
            return;
        }
        if (!preg_match('/^\d{4}$/', $pin)) {
            Session::flash('error', 'O PIN da unidade deve ter exatamente 4 dígitos.');
            $this->redirect('/admin/totem');
            return;
        }

        $unitModel = new Unit();
        if ($unitModel->pinExists($pin)) {
            Session::flash('error', 'Este PIN já está em uso por outra unidade.');
            $this->redirect('/admin/totem');
            return;
        }

        $id = $unitModel->create([
            'name'       => $name,
            'location'   => trim((string) $this->input('location', '')) ?: null,
            'pin'        => $pin,
            'open_time'  => $this->input('open_time', '08:00') . ':00',
            'close_time' => $this->input('close_time', '18:00') . ':00',
            'active'     => $this->input('active', '0') === '1' ? 1 : 0,
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);

        (new ActivityLog())->log('unit.created', 'unit', $id, "Unidade '{$name}' criada");
        Session::flash('success', 'Unidade criada com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Atualiza uma unidade.
     */
    public function updateUnit(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $unitModel = new Unit();
        $unit = $unitModel->find((int) $id);
        if (!$unit) {
            Session::flash('error', 'Unidade não encontrada.');
            $this->redirect('/admin/totem');
            return;
        }

        $name = trim((string) $this->input('name', ''));
        $pin  = trim((string) $this->input('pin', ''));

        if ($name === '') {
            Session::flash('error', 'O nome da unidade é obrigatório.');
            $this->redirect('/admin/totem');
            return;
        }
        if (!preg_match('/^\d{4}$/', $pin)) {
            Session::flash('error', 'O PIN da unidade deve ter exatamente 4 dígitos.');
            $this->redirect('/admin/totem');
            return;
        }
        if ($unitModel->pinExists($pin, (int) $id)) {
            Session::flash('error', 'Este PIN já está em uso por outra unidade.');
            $this->redirect('/admin/totem');
            return;
        }

        $unitModel->update((int) $id, [
            'name'       => $name,
            'location'   => trim((string) $this->input('location', '')) ?: null,
            'pin'        => $pin,
            'open_time'  => $this->input('open_time', '08:00') . ':00',
            'close_time' => $this->input('close_time', '18:00') . ':00',
            'active'     => $this->input('active', '0') === '1' ? 1 : 0,
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);

        (new ActivityLog())->log('unit.updated', 'unit', (int) $id, "Unidade '{$name}' atualizada");
        Session::flash('success', 'Unidade atualizada com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Exclui uma unidade (e suas salas, via cascade).
     */
    public function deleteUnit(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $unitModel = new Unit();
        $unit = $unitModel->find((int) $id);
        if (!$unit) {
            Session::flash('error', 'Unidade não encontrada.');
            $this->redirect('/admin/totem');
            return;
        }

        if ($unitModel->count() <= 1) {
            Session::flash('error', 'Não é possível excluir a única unidade existente.');
            $this->redirect('/admin/totem');
            return;
        }

        $unitModel->delete((int) $id);
        (new ActivityLog())->log('unit.deleted', 'unit', (int) $id, "Unidade '{$unit->name}' excluída");
        Session::flash('success', 'Unidade excluída com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Duplica as salas de uma unidade para outra (usa salas existentes como padrão).
     */
    public function cloneRooms(): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $fromUnit = (int) $this->input('from_unit', 0);
        $toUnit   = (int) $this->input('to_unit', 0);

        if ($fromUnit <= 0 || $toUnit <= 0 || $fromUnit === $toUnit) {
            Session::flash('error', 'Selecione unidades de origem e destino diferentes.');
            $this->redirect('/admin/totem');
            return;
        }

        $rooms = $this->roomModel->allOrdered($fromUnit);
        $itemModel = new RoomItem();
        $count = 0;

        foreach ($rooms as $room) {
            $newId = $this->roomModel->create([
                'unit_id'       => $toUnit,
                'name'          => $room->name,
                'description'   => $room->description,
                'icon'          => $room->icon,
                'image_path'    => $room->image_path ?? null,
                'capacity'      => $room->capacity,
                'active'        => (int) $room->active,
                'show_in_totem' => (int) $room->show_in_totem,
                'sort_order'    => (int) $room->sort_order,
            ]);
            // Clona os itens da sala
            foreach ($itemModel->getByRoom((int) $room->id) as $it) {
                $itemModel->create([
                    'room_id'     => $newId,
                    'name'        => $it->name,
                    'description' => $it->description,
                    'icon'        => $it->icon,
                    'active'      => (int) $it->active,
                    'sort_order'  => (int) $it->sort_order,
                ]);
            }
            $count++;
        }

        (new ActivityLog())->log('unit.clone_rooms', 'unit', $toUnit, "Clonadas {$count} salas para a unidade #{$toUnit}");
        Session::flash('success', "{$count} sala(s) copiada(s) com sucesso!");
        $this->redirect('/admin/totem');
    }

    /**
     * Salva as configurações gerais do totem.
     */
    public function save(): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $pin = trim((string) $this->input('totem_pin', ''));
        if (!preg_match('/^\d{4}$/', $pin)) {
            Session::flash('error', 'O PIN deve conter exatamente 4 dígitos numéricos.');
            $this->redirect('/admin/totem');
            return;
        }

        // Coerência das durações
        $slot    = max(5, (int) $this->input('totem_slot_minutes', 30));
        $min     = (int) $this->input('totem_min_duration', 30);
        $max     = (int) $this->input('totem_max_duration', 120);
        $default = (int) $this->input('totem_default_duration', 30);

        if ($min > $max) {
            Session::flash('error', 'O tempo mínimo não pode ser maior que o tempo máximo.');
            $this->redirect('/admin/totem');
            return;
        }
        if ($default < $min || $default > $max) {
            Session::flash('error', 'O tempo padrão deve estar entre o mínimo e o máximo.');
            $this->redirect('/admin/totem');
            return;
        }
        foreach (['padrão' => $default, 'mínimo' => $min, 'máximo' => $max] as $nome => $val) {
            if ($val % $slot !== 0) {
                Session::flash('error', "O tempo {$nome} deve ser múltiplo do intervalo das janelas ({$slot} min).");
                $this->redirect('/admin/totem');
                return;
            }
        }

        $settings = [
            'totem_enabled'         => $this->input('totem_enabled', '0'),
            'totem_pin'             => $pin,
            'totem_open_time'       => $this->input('totem_open_time', '08:00'),
            'totem_close_time'      => $this->input('totem_close_time', '18:00'),
            'totem_slot_minutes'    => (int) $this->input('totem_slot_minutes', 30),
            'totem_buffer_minutes'  => (int) $this->input('totem_buffer_minutes', 0),
            'totem_default_duration'=> (int) $this->input('totem_default_duration', 30),
            'totem_min_duration'    => (int) $this->input('totem_min_duration', 30),
            'totem_max_duration'    => (int) $this->input('totem_max_duration', 120),
            'totem_advance_minutes' => (int) $this->input('totem_advance_minutes', 0),
            'totem_require_email'   => $this->input('totem_require_email', '0'),
            'totem_refresh_seconds' => (int) $this->input('totem_refresh_seconds', 15),
            // Notificações
            'smtp_enabled'          => $this->input('smtp_enabled', '0'),
            'smtp_host'             => trim((string) $this->input('smtp_host', '')),
            'smtp_port'             => (int) $this->input('smtp_port', 587),
            'smtp_encryption'       => $this->input('smtp_encryption', 'tls'),
            'smtp_username'         => trim((string) $this->input('smtp_username', '')),
            'smtp_from_email'       => trim((string) $this->input('smtp_from_email', '')),
            'smtp_from_name'        => trim((string) $this->input('smtp_from_name', '')),
            'webhook_enabled'       => $this->input('webhook_enabled', '0'),
            'webhook_url'           => trim((string) $this->input('webhook_url', '')),
        ];

        // Senha SMTP: só atualiza se preenchida (evita apagar ao salvar)
        $smtpPass = (string) $this->input('smtp_password', '');
        if ($smtpPass !== '') {
            $settings['smtp_password'] = $smtpPass;
        }

        // Upload do logo (PNG)
        $logoPath = $this->handleImageUpload('totem_logo', 'logo');
        if ($logoPath !== null) {
            $settings['totem_logo'] = $logoPath;
        }

        $this->settingModel->saveMultiple($settings);

        $log = new ActivityLog();
        $log->log('totem.settings_updated', 'settings', null, 'Configurações do Totem atualizadas');

        Session::flash('success', 'Configurações do Totem salvas com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Faz upload de uma imagem PNG e retorna o caminho público (/uploads/...).
     * Retorna null se nenhum arquivo foi enviado.
     */
    private function handleImageUpload(string $field, string $prefix): ?string
    {
        if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES[$field];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Falha no upload da imagem.');
            return null;
        }

        // Valida tipo real (PNG)
        $info = @getimagesize($file['tmp_name']);
        if ($info === false || ($info['mime'] ?? '') !== 'image/png') {
            Session::flash('error', 'A imagem deve ser um arquivo PNG.');
            return null;
        }

        // Limite de 4 MB
        if ($file['size'] > 4 * 1024 * 1024) {
            Session::flash('error', 'A imagem não pode exceder 4 MB.');
            return null;
        }

        $dir = __DIR__ . '/../../public/uploads';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $filename = $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.png';
        $dest = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Session::flash('error', 'Não foi possível salvar a imagem.');
            return null;
        }

        return '/uploads/' . $filename;
    }

    /**
     * Cria uma nova sala.
     */
    public function storeRoom(): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'O nome da sala é obrigatório.');
            $this->redirect('/admin/totem');
            return;
        }

        $data = [
            'unit_id'       => max(1, (int) $this->input('unit_id', 1)),
            'name'          => $name,
            'description'   => trim((string) $this->input('description', '')) ?: null,
            'icon'          => trim((string) $this->input('icon', 'bi-easel')) ?: 'bi-easel',
            'capacity'      => $this->input('capacity') !== '' ? (int) $this->input('capacity') : null,
            'active'        => $this->input('active', '0') === '1' ? 1 : 0,
            'show_in_totem' => $this->input('show_in_totem', '0') === '1' ? 1 : 0,
            'sort_order'    => (int) $this->input('sort_order', 0),
        ];

        $img = $this->handleImageUpload('image', 'room');
        if ($img !== null) {
            $data['image_path'] = $img;
        }

        $id = $this->roomModel->create($data);

        $log = new ActivityLog();
        $log->log('room.created', 'room', $id, "Sala '{$name}' criada");

        Session::flash('success', 'Sala adicionada com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Atualiza uma sala existente.
     */
    public function updateRoom(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $room = $this->roomModel->find((int) $id);
        if (!$room) {
            Session::flash('error', 'Sala não encontrada.');
            $this->redirect('/admin/totem');
            return;
        }

        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'O nome da sala é obrigatório.');
            $this->redirect('/admin/totem');
            return;
        }

        $data = [
            'unit_id'       => max(1, (int) $this->input('unit_id', (int) $room->unit_id)),
            'name'          => $name,
            'description'   => trim((string) $this->input('description', '')) ?: null,
            'icon'          => trim((string) $this->input('icon', 'bi-easel')) ?: 'bi-easel',
            'capacity'      => $this->input('capacity') !== '' ? (int) $this->input('capacity') : null,
            'active'        => $this->input('active', '0') === '1' ? 1 : 0,
            'show_in_totem' => $this->input('show_in_totem', '0') === '1' ? 1 : 0,
            'sort_order'    => (int) $this->input('sort_order', 0),
        ];

        if ($this->input('remove_image', '0') === '1') {
            $data['image_path'] = null;
        }
        $img = $this->handleImageUpload('image', 'room');
        if ($img !== null) {
            $data['image_path'] = $img;
        }

        $this->roomModel->update((int) $id, $data);

        $log = new ActivityLog();
        $log->log('room.updated', 'room', (int) $id, "Sala '{$name}' atualizada");

        Session::flash('success', 'Sala atualizada com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Exclui uma sala.
     */
    public function deleteRoom(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $room = $this->roomModel->find((int) $id);
        if (!$room) {
            Session::flash('error', 'Sala não encontrada.');
            $this->redirect('/admin/totem');
            return;
        }

        $this->roomModel->delete((int) $id);

        $log = new ActivityLog();
        $log->log('room.deleted', 'room', (int) $id, "Sala '{$room->name}' excluída");

        Session::flash('success', 'Sala excluída com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Tela de gestão dos itens de demonstração de uma sala.
     */
    public function items(string $id): void
    {
        $this->requireSuperAdmin();

        $room = $this->roomModel->find((int) $id);
        if (!$room) {
            Session::flash('error', 'Sala não encontrada.');
            $this->redirect('/admin/totem');
            return;
        }

        $itemModel = new RoomItem();
        $this->view('admin/totem_items', [
            'room'  => $room,
            'items' => $itemModel->getByRoom((int) $id),
        ]);
    }

    /**
     * Cria um item de demonstração para uma sala.
     */
    public function storeItem(): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $roomId = (int) $this->input('room_id', 0);
        $room = $this->roomModel->find($roomId);
        if (!$room) {
            Session::flash('error', 'Sala não encontrada.');
            $this->redirect('/admin/totem');
            return;
        }

        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'O nome do item é obrigatório.');
            $this->redirect("/admin/totem/rooms/{$roomId}/items");
            return;
        }

        $itemModel = new RoomItem();
        $itemId = $itemModel->create([
            'room_id'     => $roomId,
            'name'        => $name,
            'description' => trim((string) $this->input('description', '')) ?: null,
            'icon'        => trim((string) $this->input('icon', 'bi-check2-circle')) ?: 'bi-check2-circle',
            'active'      => $this->input('active', '0') === '1' ? 1 : 0,
            'sort_order'  => (int) $this->input('sort_order', 0),
        ]);

        $log = new ActivityLog();
        $log->log('room_item.created', 'room_item', $itemId, "Item '{$name}' adicionado à sala '{$room->name}'");

        Session::flash('success', 'Item adicionado com sucesso!');
        $this->redirect("/admin/totem/rooms/{$roomId}/items");
    }

    /**
     * Atualiza um item de demonstração.
     */
    public function updateItem(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $itemModel = new RoomItem();
        $item = $itemModel->find((int) $id);
        if (!$item) {
            Session::flash('error', 'Item não encontrado.');
            $this->redirect('/admin/totem');
            return;
        }

        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'O nome do item é obrigatório.');
            $this->redirect("/admin/totem/rooms/{$item->room_id}/items");
            return;
        }

        $itemModel->update((int) $id, [
            'name'        => $name,
            'description' => trim((string) $this->input('description', '')) ?: null,
            'icon'        => trim((string) $this->input('icon', 'bi-check2-circle')) ?: 'bi-check2-circle',
            'active'      => $this->input('active', '0') === '1' ? 1 : 0,
            'sort_order'  => (int) $this->input('sort_order', 0),
        ]);

        $log = new ActivityLog();
        $log->log('room_item.updated', 'room_item', (int) $id, "Item '{$name}' atualizado");

        Session::flash('success', 'Item atualizado com sucesso!');
        $this->redirect("/admin/totem/rooms/{$item->room_id}/items");
    }

    /**
     * Exclui um item de demonstração.
     */
    public function deleteItem(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $itemModel = new RoomItem();
        $item = $itemModel->find((int) $id);
        if (!$item) {
            Session::flash('error', 'Item não encontrado.');
            $this->redirect('/admin/totem');
            return;
        }

        $roomId = (int) $item->room_id;
        $itemModel->delete((int) $id);

        $log = new ActivityLog();
        $log->log('room_item.deleted', 'room_item', (int) $id, "Item '{$item->name}' excluído");

        Session::flash('success', 'Item excluído com sucesso!');
        $this->redirect("/admin/totem/rooms/{$roomId}/items");
    }

    /**
     * Cria um vendedor.
     */
    public function storeSeller(): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'O nome do vendedor é obrigatório.');
            $this->redirect('/admin/totem');
            return;
        }

        $sellerModel = new Seller();
        $id = $sellerModel->create([
            'unit_id'    => max(1, (int) $this->input('unit_id', 1)),
            'name'       => $name,
            'email'      => trim((string) $this->input('email', '')) ?: null,
            'phone'      => trim((string) $this->input('phone', '')) ?: null,
            'active'     => $this->input('active', '0') === '1' ? 1 : 0,
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);

        $log = new ActivityLog();
        $log->log('seller.created', 'seller', $id, "Vendedor '{$name}' criado");

        Session::flash('success', 'Vendedor adicionado com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Atualiza um vendedor.
     */
    public function updateSeller(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $sellerModel = new Seller();
        $seller = $sellerModel->find((int) $id);
        if (!$seller) {
            Session::flash('error', 'Vendedor não encontrado.');
            $this->redirect('/admin/totem');
            return;
        }

        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'O nome do vendedor é obrigatório.');
            $this->redirect('/admin/totem');
            return;
        }

        $sellerModel->update((int) $id, [
            'unit_id'    => max(1, (int) $this->input('unit_id', (int) $seller->unit_id)),
            'name'       => $name,
            'email'      => trim((string) $this->input('email', '')) ?: null,
            'phone'      => trim((string) $this->input('phone', '')) ?: null,
            'active'     => $this->input('active', '0') === '1' ? 1 : 0,
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);

        $log = new ActivityLog();
        $log->log('seller.updated', 'seller', (int) $id, "Vendedor '{$name}' atualizado");

        Session::flash('success', 'Vendedor atualizado com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Exclui um vendedor.
     */
    public function deleteSeller(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem');
            return;
        }

        $sellerModel = new Seller();
        $seller = $sellerModel->find((int) $id);
        if (!$seller) {
            Session::flash('error', 'Vendedor não encontrado.');
            $this->redirect('/admin/totem');
            return;
        }

        $sellerModel->delete((int) $id);

        $log = new ActivityLog();
        $log->log('seller.deleted', 'seller', (int) $id, "Vendedor '{$seller->name}' excluído");

        Session::flash('success', 'Vendedor excluído com sucesso!');
        $this->redirect('/admin/totem');
    }

    /**
     * Aba "Logs de Envio" — histórico de e-mails e webhooks.
     */
    public function logs(): void
    {
        $this->requireSuperAdmin();

        $logModel = new NotificationLog();
        $this->view('admin/totem_logs', [
            'logs' => $logModel->getRecent(200),
        ]);
    }

    /**
     * Lista de reservas (agendamentos) para administração.
     */
    public function reservations(): void
    {
        $this->requireSuperAdmin();

        $unitId = (int) $this->query('unit', 0);
        $selectedUnit = $unitId > 0 ? $unitId : null;

        $reservationModel = new \App\Models\RoomReservation();
        $this->view('admin/totem_reservations', [
            'reservations' => $reservationModel->getRecentWithRoom(300, $selectedUnit),
            'units'        => (new Unit())->allOrdered(),
            'selectedUnit' => $selectedUnit,
        ]);
    }

    /**
     * Detalhe de uma reserva em JSON (para o modal do admin/calendário).
     */
    public function reservationInfo(string $id): void
    {
        $this->requireSuperAdmin();

        $reservationModel = new \App\Models\RoomReservation();
        $r = $reservationModel->findWithRoom((int) $id);
        if (!$r) {
            $this->json(['success' => false, 'message' => 'Reserva não encontrada.'], 404);
            return;
        }

        $sellerPhone = null;
        if (!empty($r->seller_id)) {
            $seller = (new Seller())->find((int) $r->seller_id);
            $sellerPhone = $seller->phone ?? null;
        }

        $this->json([
            'success' => true,
            'reservation' => [
                'id'             => (int) $r->id,
                'room'           => $r->room_name,
                'date'           => date('d/m/Y', strtotime($r->reservation_date)),
                'start'          => substr($r->start_time, 0, 5),
                'end'            => substr($r->end_time, 0, 5),
                'customer_name'  => $r->customer_name,
                'customer_phone' => $r->customer_phone,
                'customer_email' => $r->customer_email,
                'seller_name'    => $r->seller_name,
                'seller_phone'   => $sellerPhone,
                'interest'       => $r->interest,
                'status'         => $r->status,
                'source'         => $r->source,
                'created_at'     => date('d/m/Y H:i', strtotime($r->created_at)),
            ],
        ]);
    }

    /**
     * Aba "Logs de Ações" — auditoria de alterações nas reservas.
     */
    public function auditLogs(): void
    {
        $this->requireSuperAdmin();

        $auditModel = new \App\Models\ReservationAudit();
        $this->view('admin/totem_audit', [
            'entries' => $auditModel->getRecent(300),
            'labels'  => \App\Models\ReservationAudit::FIELD_LABELS,
        ]);
    }

    /**
     * Reenvia uma notificação a partir de um registro do log.
     * Remonta os dados da reserva original e registra o novo resultado no log.
     */
    public function resendLog(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem/logs');
            return;
        }

        $logModel = new NotificationLog();
        $log = $logModel->find((int) $id);
        if (!$log) {
            Session::flash('error', 'Registro de log não encontrado.');
            $this->redirect('/admin/totem/logs');
            return;
        }

        $notifier = new \App\Services\NotificationService();

        // Reconstrói os dados da reserva quando disponível
        $data = $this->buildNotificationDataFromReservation($log->reservation_id ? (int) $log->reservation_id : null);

        if ($log->channel === 'webhook') {
            $data['reservation_id'] = $log->reservation_id ? (int) $log->reservation_id : null;
            $ok = $notifier->sendWebhook($data);
            Session::flash($ok ? 'success' : 'error', $ok ? 'Webhook reenviado com sucesso!' : 'Falha ao reenviar o webhook. Veja o novo registro no log.');
            $this->redirect('/admin/totem/logs');
            return;
        }

        // E-mail: reenvia para o mesmo destinatário
        if (empty($log->recipient)) {
            Session::flash('error', 'Este registro não possui destinatário para reenvio.');
            $this->redirect('/admin/totem/logs');
            return;
        }

        // Descobre se o destinatário é o vendedor ou o visitante para montar o corpo certo
        $isSeller = isset($data['seller_email']) && $data['seller_email'] === $log->recipient;
        $subject = (string) ($log->subject ?? 'Reenvio de notificação');

        $body = $isSeller
            ? $this->invokeBuild($notifier, 'buildSellerBody', $data)
            : $this->invokeBuild($notifier, 'buildCustomerBody', $data);

        $toName = $isSeller ? ($data['seller_name'] ?? '') : ($data['customer_name'] ?? '');

        $ok = $notifier->sendEmail($log->recipient, (string) $toName, $subject, $body, $log->reservation_id ? (int) $log->reservation_id : null);

        Session::flash($ok ? 'success' : 'error', $ok ? 'E-mail reenviado com sucesso!' : 'Falha ao reenviar o e-mail. Veja o novo registro no log.');
        $this->redirect('/admin/totem/logs');
    }

    /**
     * Envia um e-mail de teste para validar a configuração SMTP.
     */
    public function testEmail(): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/totem/logs');
            return;
        }

        $to = trim((string) $this->input('test_email', ''));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Informe um e-mail válido para o teste.');
            $this->redirect('/admin/totem/logs');
            return;
        }

        $notifier = new \App\Services\NotificationService();
        $body = '<div style="font-family:Arial,sans-serif;background:#111;color:#eee;padding:24px;border-radius:12px;">'
            . '<h2 style="color:#FFC107;">Teste de SMTP</h2>'
            . '<p>Este é um e-mail de teste do Totem de Reservas. Se você recebeu, o SMTP está configurado corretamente.</p>'
            . '<p style="color:#FFC107;font-weight:bold;">Agenda Beessential</p></div>';

        $ok = $notifier->sendEmail($to, 'Teste', 'Teste de configuração SMTP - Totem', $body);

        Session::flash($ok ? 'success' : 'error', $ok
            ? "E-mail de teste enviado para {$to}. Confira a caixa de entrada."
            : 'Falha no envio de teste. Veja o motivo no log abaixo.');
        $this->redirect('/admin/totem/logs');
    }

    /**
     * Monta os dados de notificação a partir de uma reserva salva.
     * Se a reserva não existir mais, retorna um conjunto mínimo.
     */
    private function buildNotificationDataFromReservation(?int $reservationId): array
    {
        if (!$reservationId) {
            return [];
        }

        $reservationModel = new \App\Models\RoomReservation();
        $r = $reservationModel->find($reservationId);
        if (!$r) {
            return [];
        }

        $roomModel = new Room();
        $room = $roomModel->find((int) $r->room_id);

        $itemModel = new RoomItem();
        $items = array_map(fn($it) => [
            'name'        => $it->name,
            'description' => $it->description,
        ], $itemModel->getActiveByRoom((int) $r->room_id));

        $sellerEmail = null;
        $sellerPhone = null;
        if (!empty($r->seller_id)) {
            $sellerModel = new Seller();
            $seller = $sellerModel->find((int) $r->seller_id);
            if ($seller) {
                $sellerEmail = $seller->email ?? null;
                $sellerPhone = $seller->phone ?? null;
            }
        }

        return [
            'reservation_id' => (int) $r->id,
            'room'           => $room ? $room->name : '',
            'date'           => date('d/m/Y', strtotime($r->reservation_date)),
            'start'          => substr($r->start_time, 0, 5),
            'end'            => substr($r->end_time, 0, 5),
            'customer_name'  => $r->customer_name,
            'customer_phone' => $r->customer_phone,
            'customer_email' => $r->customer_email,
            'seller_id'      => $r->seller_id ? (int) $r->seller_id : null,
            'seller_name'    => $r->seller_name,
            'seller_email'   => $sellerEmail,
            'seller_phone'   => $sellerPhone,
            'interest'       => $r->interest ?? null,
            'items'          => $items,
        ];
    }

    /**
     * Chama um método protegido de montagem de corpo do NotificationService.
     */
    private function invokeBuild(object $notifier, string $method, array $data): string
    {
        $ref = new \ReflectionMethod($notifier, $method);
        $ref->setAccessible(true);
        return (string) $ref->invoke($notifier, $data);
    }
}
