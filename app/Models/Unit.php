<?php

namespace App\Models;

use App\Core\Model;

class Unit extends Model
{
    protected string $table = 'units';

    /**
     * Unidades ativas ordenadas.
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE active = 1 ORDER BY sort_order ASC, name ASC";
        return $this->db()->query($sql);
    }

    /**
     * Todas as unidades ordenadas (administração).
     */
    public function allOrdered(): array
    {
        return $this->all('sort_order ASC, name ASC');
    }

    /**
     * Encontra uma unidade ativa pelo PIN.
     */
    public function findByPin(string $pin): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE pin = :pin AND active = 1 LIMIT 1";
        $result = $this->db()->query($sql, ['pin' => $pin]);
        return $result[0] ?? null;
    }

    /**
     * Verifica se um PIN já está em uso por outra unidade.
     */
    public function pinExists(string $pin, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} WHERE pin = :pin";
        $params = ['pin' => $pin];
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        $result = $this->db()->query($sql, $params);
        return (int) $result[0]->total > 0;
    }
}
