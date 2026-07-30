<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

/**
 * Middleware de autenticação.
 * Pode ser utilizado para proteger rotas que exigem login.
 */
class AuthMiddleware
{
    /**
     * Verifica se o usuário está autenticado.
     * Redireciona para login caso contrário.
     */
    public static function handle(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Você precisa estar logado para acessar esta página.');
            header('Location: /login');
            exit;
        }
        return true;
    }

    /**
     * Verifica se o usuário é superadmin.
     */
    public static function requireSuperAdmin(): bool
    {
        self::handle();

        if (!Auth::isSuperAdmin()) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
        return true;
    }

    /**
     * Verifica se o usuário tem determinada permissão.
     */
    public static function requirePermission(string $permission): bool
    {
        self::handle();

        if (!Auth::hasPermission($permission)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
        return true;
    }
}
