<?php

namespace App\Core;

use App\Models\User;
use App\Models\Permission;

/**
 * Classe de autenticação.
 * Gerencia login, logout e verificação de permissões.
 */
class Auth
{
    /**
     * Tenta autenticar o usuário com email e senha.
     */
    public static function attempt(string $email, string $password): bool
    {
        $userModel = new User();
        $user = $userModel->findBy('email', $email);

        if (!$user) {
            return false;
        }

        if ((int)$user->active !== 1) {
            return false;
        }

        if (!password_verify($password, $user->password)) {
            return false;
        }

        // Login bem-sucedido
        self::loginUser($user);
        return true;
    }

    /**
     * Registra o usuário na sessão.
     */
    private static function loginUser(object $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user->id);
        Session::set('user_name', $user->name);
        Session::set('user_email', $user->email);
        Session::set('user_role_id', $user->role_id);

        // Carrega permissões do role
        $permissionModel = new Permission();
        $permissions = $permissionModel->getPermissionsByRoleId($user->role_id);
        Session::set('user_permissions', $permissions);

        // Atualiza último login
        $userModel = new User();
        $userModel->update($user->id, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Verifica se há um usuário autenticado.
     */
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Retorna o ID do usuário logado.
     */
    public static function userId(): ?int
    {
        return Session::get('user_id');
    }

    /**
     * Retorna o nome do usuário logado.
     */
    public static function userName(): ?string
    {
        return Session::get('user_name');
    }

    /**
     * Retorna o email do usuário logado.
     */
    public static function userEmail(): ?string
    {
        return Session::get('user_email');
    }

    /**
     * Retorna o role_id do usuário logado.
     */
    public static function userRoleId(): ?int
    {
        return Session::get('user_role_id');
    }

    /**
     * Verifica se o usuário é superadmin (role_id = 1).
     */
    public static function isSuperAdmin(): bool
    {
        return (int) Session::get('user_role_id') === 1;
    }

    /**
     * Verifica se o usuário tem uma determinada permissão.
     */
    public static function hasPermission(string $permission): bool
    {
        // Superadmin tem todas as permissões
        if (self::isSuperAdmin()) {
            return true;
        }

        $permissions = Session::get('user_permissions', []);
        return in_array($permission, $permissions, true);
    }

    /**
     * Realiza o logout do usuário.
     */
    public static function logout(): void
    {
        Session::destroy();
    }

    /**
     * Retorna o objeto completo do usuário logado.
     */
    public static function user(): ?object
    {
        if (!self::check()) {
            return null;
        }
        $userModel = new User();
        return $userModel->find(self::userId());
    }
}
