<?php

namespace App\Core;

/**
 * Controller base.
 * Todos os controllers da aplicação devem estender esta classe.
 */
abstract class Controller
{
    /**
     * Renderiza uma view com dados.
     */
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /**
     * Redireciona para outra URL.
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Retorna resposta JSON.
     */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Envia a resposta JSON ao cliente e fecha a conexão, permitindo que o
     * servidor continue processando tarefas lentas (e-mail, webhook) em segundo plano.
     */
    protected function jsonThenContinue(mixed $data, int $status = 200): void
    {
        ignore_user_abort(true);

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Connection: close');

        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        header('Content-Length: ' . strlen($payload));

        echo $payload;

        // Descarrega o buffer e encerra a conexão com o navegador
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            @ob_end_flush();
            @flush();
        }
    }

    /**
     * Obtém dados do POST.
     */
    protected function input(string $key, $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtém dados do GET (query string).
     */
    protected function query(string $key, $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Valida token CSRF.
     */
    protected function validateCsrf(): bool
    {
        $token = $this->input('_csrf_token');
        return Session::validateCsrfToken($token);
    }

    /**
     * Exige que o usuário esteja autenticado.
     */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Você precisa estar logado para acessar esta página.');
            $this->redirect('/login');
        }
    }

    /**
     * Exige que o usuário tenha determinada permissão.
     */
    protected function requirePermission(string $permission): void
    {
        $this->requireAuth();
        if (!Auth::hasPermission($permission)) {
            http_response_code(403);
            $this->view('errors/403');
            exit;
        }
    }

    /**
     * Exige que o usuário seja superadmin.
     */
    protected function requireSuperAdmin(): void
    {
        $this->requireAuth();
        if (!Auth::isSuperAdmin()) {
            http_response_code(403);
            $this->view('errors/403');
            exit;
        }
    }
}
