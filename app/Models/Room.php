<?php

namespace App\Models;

use App\Core\Model;

class Room extends Model
{
    protected string $table = 'rooms';

    /**
     * Retorna as salas visíveis no totem (ativas e marcadas para exibição),
     * opcionalmente filtradas por unidade.
     */
    public function getTotemRooms(?int $unitId = null): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE active = 1 AND show_in_totem = 1";
        $params = [];
        if ($unitId !== null) {
            $sql .= " AND unit_id = :unit_id";
            $params['unit_id'] = $unitId;
        }
        $sql .= " ORDER BY name ASC";
        return $this->db()->query($sql, $params);
    }

    /**
     * Retorna as salas ordenadas (para administração), opcionalmente por unidade.
     */
    public function allOrdered(?int $unitId = null): array
    {
        if ($unitId !== null) {
            $sql = "SELECT * FROM {$this->table} WHERE unit_id = :unit_id ORDER BY sort_order ASC, name ASC";
            return $this->db()->query($sql, ['unit_id' => $unitId]);
        }
        return $this->all('sort_order ASC, name ASC');
    }

    /**
     * Salas com o nome da unidade (para administração global).
     */
    public function allWithUnit(): array
    {
        $sql = "SELECT r.*, u.name AS unit_name
                FROM {$this->table} r
                LEFT JOIN units u ON r.unit_id = u.id
                ORDER BY u.sort_order ASC, r.sort_order ASC, r.name ASC";
        return $this->db()->query($sql);
    }
}
