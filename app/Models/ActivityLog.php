<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Auth;

class ActivityLog extends Model
{
    protected string $table = 'activity_log';

    /**
     * Registra uma atividade no log.
     */
    public function log(string $action, ?string $entityType = null, ?int $entityId = null, ?string $description = null): int
    {
        return $this->create([
            'user_id'     => Auth::userId(),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'description' => $description,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    /**
     * Retorna os últimos registros do log.
     */
    public function getRecent(int $limit = 20): array
    {
        $sql = "SELECT al.*, u.name as user_name
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT {$limit}";
        return $this->db()->query($sql);
    }

    /**
     * Retorna logs de um usuário específico.
     */
    public function getByUser(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT {$limit}";
        return $this->db()->query($sql, ['user_id' => $userId]);
    }
}
