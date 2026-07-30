<?php

namespace App\Models;

use App\Core\Model;

class Seller extends Model
{
    protected string $table = 'sellers';

    /**
     * Vendedores ativos (para o dropdown do totem).
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE active = 1
                ORDER BY sort_order ASC, name ASC";
        return $this->db()->query($sql);
    }

    /**
     * Todos os vendedores ordenados (administração).
     */
    public function allOrdered(): array
    {
        return $this->all('sort_order ASC, name ASC');
    }
}
