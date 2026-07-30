<?php

namespace App\Core;

/**
 * Roteador da aplicação.
 * Mapeia URIs para controllers e resolve parâmetros dinâmicos.
 */
class Router
{
    private array $routes = [];

    public function addRoute(string $method, string $uri, array $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'uri'     => $uri,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri    = $this->getRequestUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $params = $this->matchUri($route['uri'], $requestUri);
            if ($params !== false) {
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // 404 - Rota não encontrada
        http_response_code(404);
        View::render('errors/404');
    }

    private function getRequestUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove base path se a aplicação estiver em subdiretório
        $basePath = $this->getBasePath();
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    private function getBasePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);

        // Normaliza
        if ($basePath === '\\' || $basePath === '/') {
            return '';
        }

        return rtrim($basePath, '/');
    }

    /**
     * Tenta casar a URI da rota com a URI da requisição.
     * Retorna array de parâmetros em caso de sucesso, false caso contrário.
     */
    private function matchUri(string $routeUri, string $requestUri): array|false
    {
        // Converte {param} em regex nomeada
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routeUri);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestUri, $matches)) {
            // Filtra apenas parâmetros nomeados
            return array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    private function executeHandler(array $handler, array $params): void
    {
        [$controllerClass, $method] = $handler;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller não encontrado: {$controllerClass}");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Método não encontrado: {$controllerClass}::{$method}");
        }

        call_user_func_array([$controller, $method], $params);
    }
}
