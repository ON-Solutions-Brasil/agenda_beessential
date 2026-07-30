<?php

namespace App\Models;

use App\Core\Model;

class Permission extends Model
{
    protected string $table = 'permissions';

    /**
     * Retorna todas as permissões agrupadas por grupo.
     */
    public function allGrouped(): array
    {
        $permissions = $this->all('group_name ASC, name ASC');
        $grouped = [];

        foreach ($permissions as $perm) {
            $grouped[$perm->group_name][] = $perm;
        }

        return $grouped;
    }

    /**
     * Retorna os slugs de permissões de um role.
     */
    public function getPermissionsByRoleId(int $roleId): array
    {
        $sql = "SELECT p.slug 
                FROM {$this->table} p 
                INNER JOIN role_permissions rp ON rp.permission_id = p.id 
                WHERE rp.role_id = :role_id";
        $results = $this->db()->query($sql, ['role_id' => $roleId]);

        return array_map(fn($row) => $row->slug, $results);
    }

    /**
     * Retorna IDs das permissões de um role.
     */
    public function getPermissionIdsByRoleId(int $roleId): array
    {
        $sql = "SELECT permission_id FROM role_permissions WHERE role_id = :role_id";
        $results = $this->db()->query($sql, ['role_id' => $roleId]);
        return array_map(fn($row) => (int) $row->permission_id, $results);
    }
}
