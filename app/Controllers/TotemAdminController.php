<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Setting;
use App\Models\Room;
use App\Models\RoomItem;
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
            'totem_slot_minutes', 'totem_default_duration', 'totem_min_duration',
            'totem_max_duration', 'totem_advance_minutes', 'totem_require_email',
            'totem_refresh_seconds',
        ];

        $config = [];
        foreach ($keys as $key) {
            $config[$key] = $this->settingModel->getValue($key);
        }

        $this->view('admin/totem', [
            'config' => $config,
            'rooms'  => $this->roomModel->allOrdered(),
        ]);
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

        $this->settingModel->saveMultiple([
            'totem_enabled'         => $this->input('totem_enabled', '0'),
            'totem_pin'             => $pin,
            'totem_open_time'       => $this->input('totem_open_time', '08:00'),
            'totem_close_time'      => $this->input('totem_close_time', '18:00'),
            'totem_slot_minutes'    => (int) $this->input('totem_slot_minutes', 30),
            'totem_default_duration'=> (int) $this->input('totem_default_duration', 30),
            'totem_min_duration'    => (int) $this->input('totem_min_duration', 30),
            'totem_max_duration'    => (int) $this->input('totem_max_duration', 120),
            'totem_advance_minutes' => (int) $this->input('totem_advance_minutes', 0),
            'totem_require_email'   => $this->input('totem_require_email', '0'),
            'totem_refresh_seconds' => (int) $this->input('totem_refresh_seconds', 15),
        ]);

        $log = new ActivityLog();
        $log->log('totem.settings_updated', 'settings', null, 'Configurações do Totem atualizadas');

        Session::flash('success', 'Configurações do Totem salvas com sucesso!');
        $this->redirect('/admin/totem');
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

        $id = $this->roomModel->create([
            'name'          => $name,
            'description'   => trim((string) $this->input('description', '')) ?: null,
            'icon'          => trim((string) $this->input('icon', 'bi-easel')) ?: 'bi-easel',
            'capacity'      => $this->input('capacity') !== '' ? (int) $this->input('capacity') : null,
            'active'        => $this->input('active', '0') === '1' ? 1 : 0,
            'show_in_totem' => $this->input('show_in_totem', '0') === '1' ? 1 : 0,
            'sort_order'    => (int) $this->input('sort_order', 0),
        ]);

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

        $this->roomModel->update((int) $id, [
            'name'          => $name,
            'description'   => trim((string) $this->input('description', '')) ?: null,
            'icon'          => trim((string) $this->input('icon', 'bi-easel')) ?: 'bi-easel',
            'capacity'      => $this->input('capacity') !== '' ? (int) $this->input('capacity') : null,
            'active'        => $this->input('active', '0') === '1' ? 1 : 0,
            'show_in_totem' => $this->input('show_in_totem', '0') === '1' ? 1 : 0,
            'sort_order'    => (int) $this->input('sort_order', 0),
        ]);

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
}
