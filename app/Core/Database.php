<?php

namespace App\Core;

/**
 * Singleton de conexão com o banco de dados via PDO.
 */
class Database
{
    private static ?Database $instance = null;
    private \PDO $pdo;

    private function __construct()
    {
        $configPath = __DIR__ . '/../../config/database.php';

        if (!file_exists($configPath)) {
            throw new \RuntimeException("Arquivo de configuração do banco não encontrado: {$configPath}");
        }

        $config = require $configPath;

        if (!is_array($config) || empty($config['username'])) {
            throw new \RuntimeException("Configuração do banco de dados inválida ou incompleta.");
        }

        $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

        $this->pdo = new \PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Executa uma query SELECT e retorna os resultados.
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Executa um INSERT, UPDATE ou DELETE.
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Retorna o último ID inserido.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Inicia uma transação.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirma a transação.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Reverte a transação.
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
