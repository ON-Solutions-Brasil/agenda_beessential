<?php

namespace App\Models;

use App\Core\Model;

class Seller extends Model
{
    protected string $table = 'sellers';

    /**
     * Vendedores ativos (para o dropdown do totem).
     */
    public function getActive(?int $unitId = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE active = 1";
        $params = [];
        if ($unitId !== null) {
            $sql .= " AND unit_id = :unit_id";
            $params['unit_id'] = $unitId;
        }
        $sql .= " ORDER BY sort_order ASC, name ASC";
        return $this->db()->query($sql, $params);
    }

    /**
     * Todos os vendedores ordenados (administração), com o nome da unidade.
     */
    public function allOrdered(): array
    {
        $sql = "SELECT s.*, u.name AS unit_name
                FROM {$this->table} s
                LEFT JOIN units u ON s.unit_id = u.id
                ORDER BY u.sort_order ASC, s.sort_order ASC, s.name ASC";
        return $this->db()->query($sql);
    }
}
