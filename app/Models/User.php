<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Cria um novo usuário com senha hasheada.
     */
    public function createUser(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->create($data);
    }

    /**
     * Atualiza usuário. Se a senha for informada, faz hash.
     */
    public function updateUser(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        return $this->update($id, $data);
    }

    /**
     * Lista todos os usuários com o nome do role.
     */
    public function allWithRole(string $orderBy = 'users.name ASC'): array
    {
        $sql = "SELECT users.*, roles.name as role_name 
                FROM {$this->table} 
                LEFT JOIN roles ON users.role_id = roles.id 
                ORDER BY {$orderBy}";
        return $this->db()->query($sql);
    }

    /**
     * Busca usuários ativos.
     */
    public function getActiveUsers(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE active = 1 ORDER BY name ASC";
        return $this->db()->query($sql);
    }

    /**
     * Busca usuários por role.
     */
    public function getByRole(int $roleId): array
    {
        return $this->where('role_id', $roleId);
    }

    /**
     * Verifica se o email já existe (excluindo um ID específico).
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE email = :email";
        $params = ['email' => $email];

        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $result = $this->db()->query($sql, $params);
        return (int) $result[0]->total > 0;
    }
}
