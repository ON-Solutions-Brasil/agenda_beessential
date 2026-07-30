<?php

namespace App\Models;

use App\Core\Model;

class Room extends Model
{
    protected string $table = 'rooms';

    /**
     * Retorna as salas visíveis no totem (ativas e marcadas para exibição).
     */
    public function getTotemRooms(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE active = 1 AND show_in_totem = 1
                ORDER BY name ASC";
        return $this->db()->query($sql);
    }

    /**
     * Retorna todas as salas ordenadas (para administração).
     */
    public function allOrdered(): array
    {
        return $this->all('sort_order ASC, name ASC');
    }
}
