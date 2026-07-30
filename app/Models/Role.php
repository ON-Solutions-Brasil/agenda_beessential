<?php

namespace App\Models;

use App\Core\Model;

class Role extends Model
{
    protected string $table = 'roles';

    /**
     * Retorna todos os roles com contagem de usuários.
     */
    public function allWithUserCount(): array
    {
        $sql = "SELECT roles.*, COUNT(users.id) as user_count 
                FROM {$this->table} 
                LEFT JOIN users ON users.role_id = roles.id 
                GROUP BY roles.id 
                ORDER BY roles.id ASC";
        return $this->db()->query($sql);
    }

    /**
     * Busca role pelo slug.
     */
    public function findBySlug(string $slug): ?object
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Retorna as permissões atribuídas a um role.
     */
    public function getPermissions(int $roleId): array
    {
        $sql = "SELECT p.* 
                FROM permissions p 
                INNER JOIN role_permissions rp ON rp.permission_id = p.id 
                WHERE rp.role_id = :role_id 
                ORDER BY p.group_name, p.name";
        return $this->db()->query($sql, ['role_id' => $roleId]);
    }

    /**
     * Sincroniza permissões de um role.
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $db = $this->db();
        $db->beginTransaction();

        try {
            // Remove todas as permissões atuais
            $db->execute("DELETE FROM role_permissions WHERE role_id = :role_id", ['role_id' => $roleId]);

            // Insere as novas
            foreach ($permissionIds as $permId) {
                $db->execute(
                    "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :perm_id)",
                    ['role_id' => $roleId, 'perm_id' => $permId]
                );
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Verifica se o slug já existe (excluindo um ID).
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE slug = :slug";
        $params = ['slug' => $slug];

        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $result = $this->db()->query($sql, $params);
        return (int) $result[0]->total > 0;
    }
}
