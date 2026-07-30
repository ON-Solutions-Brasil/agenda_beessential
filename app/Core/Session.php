<?php

namespace App\Core;

/**
 * Gerenciamento de sessão.
 */
class Session
{
    /**
     * Inicia a sessão com configurações personalizadas.
     */
    public static function start(array $config = []): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $name     = $config['name'] ?? 'beessential_session';
        $lifetime = $config['lifetime'] ?? 7200;

        session_name($name);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);

        session_start();

        // Regenera o ID periodicamente para evitar fixation
        if (!isset($_SESSION['_last_regeneration'])) {
            self::regenerate();
        } elseif (time() - $_SESSION['_last_regeneration'] > 300) {
            self::regenerate();
        }
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_last_regeneration'] = time();
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Define uma mensagem flash (disponível apenas na próxima requisição).
     */
    public static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    /**
     * Obtém e remove uma mensagem flash.
     */
    public static function getFlash(string $key): ?string
    {
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }

    /**
     * Verifica se existe uma mensagem flash.
     */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Gera ou retorna o token CSRF da sessão.
     */
    public static function getCsrfToken(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Valida um token CSRF.
     */
    public static function validateCsrfToken(?string $token): bool
    {
        if ($token === null) {
            return false;
        }
        return hash_equals(self::getCsrfToken(), $token);
    }
}
