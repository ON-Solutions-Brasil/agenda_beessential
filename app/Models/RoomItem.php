<?php

namespace App\Models;

use App\Core\Model;

class RoomItem extends Model
{
    protected string $table = 'room_items';

    /**
     * Itens ativos de uma sala (para exibição no totem).
     */
    public function getActiveByRoom(int $roomId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE room_id = :room_id AND active = 1
                ORDER BY sort_order ASC, name ASC";
        return $this->db()->query($sql, ['room_id' => $roomId]);
    }

    /**
     * Todos os itens de uma sala (para administração).
     */
    public function getByRoom(int $roomId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE room_id = :room_id
                ORDER BY sort_order ASC, name ASC";
        return $this->db()->query($sql, ['room_id' => $roomId]);
    }
}
