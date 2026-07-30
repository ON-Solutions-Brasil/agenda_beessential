<?php

namespace App\Core;

/**
 * Classe principal da aplicação.
 * Inicializa sessão, carrega configurações e despacha a requisição.
 */
class App
{
    private static ?App $instance = null;
    private array $config = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->loadConfig();
        $this->setupTimezone();
        $this->setupErrorHandling();
    }

    private function loadConfig(): void
    {
        $this->config['app']      = require __DIR__ . '/../../config/app.php';
        $this->config['database'] = require __DIR__ . '/../../config/database.php';
    }

    private function setupTimezone(): void
    {
        date_default_timezone_set($this->config['app']['timezone'] ?? 'America/Sao_Paulo');
    }

    private function setupErrorHandling(): void
    {
        if ($this->config['app']['debug'] ?? false) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    public function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function run(): void
    {
        Session::start($this->config['app']['session'] ?? []);

        $router = new Router();
        $routes = require __DIR__ . '/../../config/routes.php';

        foreach ($routes as $route => $handler) {
            $parts  = preg_split('/\s+/', $route);
            $method = strtoupper($parts[0]);
            $uri    = $parts[1];
            $router->addRoute($method, $uri, $handler);
        }

        $router->dispatch();
    }
}
