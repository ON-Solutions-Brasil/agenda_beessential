<?php

namespace App\Core;

/**
 * Model base.
 * Fornece conexão PDO e métodos CRUD genéricos.
 */
abstract class Model
{
    protected static ?Database $db = null;
    protected string $table = '';
    protected string $primaryKey = 'id';

    protected function db(): Database
    {
        if (static::$db === null) {
            static::$db = Database::getInstance();
        }
        return static::$db;
    }

    /**
     * Busca todos os registros.
     */
    public function all(string $orderBy = 'id ASC'): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        return $this->db()->query($sql);
    }

    /**
     * Busca registro por ID.
     */
    public function find(int $id): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $results = $this->db()->query($sql, ['id' => $id]);
        return $results[0] ?? null;
    }

    /**
     * Busca registros com condição WHERE.
     */
    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value";
        return $this->db()->query($sql, ['value' => $value]);
    }

    /**
     * Busca o primeiro registro que atende a condição.
     */
    public function findBy(string $column, mixed $value): ?object
    {
        $results = $this->where($column, $value);
        return $results[0] ?? null;
    }

    /**
     * Insere um novo registro.
     */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db()->execute($sql, $data);

        return (int) $this->db()->lastInsertId();
    }

    /**
     * Atualiza um registro por ID.
     */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = :{$column}";
        }
        $setString = implode(', ', $sets);

        $data['id'] = $id;
        $sql = "UPDATE {$this->table} SET {$setString} WHERE {$this->primaryKey} = :id";

        return $this->db()->execute($sql, $data);
    }

    /**
     * Exclui um registro por ID.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db()->execute($sql, ['id' => $id]);
    }

    /**
     * Conta registros com condição opcional.
     */
    public function count(string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}";
        $result = $this->db()->query($sql, $params);
        return (int) ($result[0]->total ?? 0);
    }
}
