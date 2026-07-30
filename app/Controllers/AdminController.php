<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Meeting;

class AdminController extends Controller
{
    private Role $roleModel;
    private Permission $permissionModel;

    public function __construct()
    {
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
    }

    /**
     * Painel administrativo principal.
     */
    public function index(): void
    {
        $this->requireSuperAdmin();

        $userModel = new User();
        $meetingModel = new Meeting();
        $activityLog = new ActivityLog();

        $stats = [
            'total_users'    => $userModel->count(),
            'total_meetings' => $meetingModel->count(),
            'total_roles'    => $this->roleModel->count(),
            'recent_logs'    => $activityLog->getRecent(10),
        ];

        $this->view('admin/index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Lista todos os roles.
     */
    public function roles(): void
    {
        $this->requireSuperAdmin();

        $roles = $this->roleModel->allWithUserCount();

        $this->view('admin/roles', [
            'roles' => $roles,
        ]);
    }

    /**
     * Formulário de criação de role.
     */
    public function createRole(): void
    {
        $this->requireSuperAdmin();

        $permissions = $this->permissionModel->allGrouped();

        $this->view('admin/role_form', [
            'role'              => null,
            'permissions'       => $permissions,
            'rolePermissionIds' => [],
        ]);
    }

    /**
     * Salva novo role.
     */
    public function storeRole(): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/roles/create');
            return;
        }

        $name = trim($this->input('name', ''));
        $slug = trim($this->input('slug', ''));
        $description = trim($this->input('description', ''));
        $permissionIds = $this->input('permissions', []);

        if (empty($name) || empty($slug)) {
            Session::flash('error', 'Nome e slug são obrigatórios.');
            $this->redirect('/admin/roles/create');
            return;
        }

        // Slug deve ser único
        if ($this->roleModel->slugExists($slug)) {
            Session::flash('error', 'Este slug já está em uso.');
            $this->redirect('/admin/roles/create');
            return;
        }

        $roleId = $this->roleModel->create([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
        ]);

        // Sincroniza permissões
        if (!empty($permissionIds)) {
            $this->roleModel->syncPermissions($roleId, $permissionIds);
        }

        $log = new ActivityLog();
        $log->log('role.created', 'role', $roleId, "Role '{$name}' criado");

        Session::flash('success', 'Role criado com sucesso!');
        $this->redirect('/admin/roles');
    }

    /**
     * Formulário de edição de role.
     */
    public function editRole(string $id): void
    {
        $this->requireSuperAdmin();

        $role = $this->roleModel->find((int) $id);
        if (!$role) {
            Session::flash('error', 'Role não encontrado.');
            $this->redirect('/admin/roles');
            return;
        }

        $permissions = $this->permissionModel->allGrouped();
        $rolePermissionIds = $this->permissionModel->getPermissionIdsByRoleId((int) $id);

        $this->view('admin/role_form', [
            'role'              => $role,
            'permissions'       => $permissions,
            'rolePermissionIds' => $rolePermissionIds,
        ]);
    }

    /**
     * Atualiza role existente.
     */
    public function updateRole(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect("/admin/roles/{$id}/edit");
            return;
        }

        $role = $this->roleModel->find((int) $id);
        if (!$role) {
            Session::flash('error', 'Role não encontrado.');
            $this->redirect('/admin/roles');
            return;
        }

        $name = trim($this->input('name', ''));
        $slug = trim($this->input('slug', ''));
        $description = trim($this->input('description', ''));
        $permissionIds = $this->input('permissions', []);

        if (empty($name) || empty($slug)) {
            Session::flash('error', 'Nome e slug são obrigatórios.');
            $this->redirect("/admin/roles/{$id}/edit");
            return;
        }

        if ($this->roleModel->slugExists($slug, (int) $id)) {
            Session::flash('error', 'Este slug já está em uso por outro role.');
            $this->redirect("/admin/roles/{$id}/edit");
            return;
        }

        $this->roleModel->update((int) $id, [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
        ]);

        // Sincroniza permissões
        $this->roleModel->syncPermissions((int) $id, is_array($permissionIds) ? $permissionIds : []);

        $log = new ActivityLog();
        $log->log('role.updated', 'role', (int) $id, "Role '{$name}' atualizado");

        Session::flash('success', 'Role atualizado com sucesso!');
        $this->redirect('/admin/roles');
    }

    /**
     * Exclui um role.
     */
    public function deleteRole(string $id): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/roles');
            return;
        }

        $role = $this->roleModel->find((int) $id);
        if (!$role) {
            Session::flash('error', 'Role não encontrado.');
            $this->redirect('/admin/roles');
            return;
        }

        // Não permite excluir role do superadmin
        if ((int) $role->is_superadmin === 1) {
            Session::flash('error', 'Não é possível excluir o role de Super Admin.');
            $this->redirect('/admin/roles');
            return;
        }

        // Verifica se há usuários associados
        $userModel = new User();
        $usersInRole = $userModel->getByRole((int) $id);
        if (count($usersInRole) > 0) {
            Session::flash('error', 'Não é possível excluir um role que possui usuários associados.');
            $this->redirect('/admin/roles');
            return;
        }

        $this->roleModel->delete((int) $id);

        $log = new ActivityLog();
        $log->log('role.deleted', 'role', (int) $id, "Role '{$role->name}' excluído");

        Session::flash('success', 'Role excluído com sucesso!');
        $this->redirect('/admin/roles');
    }

    /**
     * Página de gerenciamento de permissões por role.
     */
    public function permissions(): void
    {
        $this->requireSuperAdmin();

        $roles = $this->roleModel->all('id ASC');
        $permissions = $this->permissionModel->allGrouped();

        // Monta mapa de permissões por role
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role->id] = $this->permissionModel->getPermissionIdsByRoleId($role->id);
        }

        $this->view('admin/permissions', [
            'roles'           => $roles,
            'permissions'     => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Sincroniza permissões de múltiplos roles.
     */
    public function syncPermissions(): void
    {
        $this->requireSuperAdmin();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/permissions');
            return;
        }

        $rolePermissions = $this->input('role_permissions', []);
        if (!is_array($rolePermissions)) {
            $rolePermissions = [];
        }

        foreach ($rolePermissions as $roleId => $permissionIds) {
            if (!is_array($permissionIds)) {
                $permissionIds = [];
            }
            $this->roleModel->syncPermissions((int) $roleId, $permissionIds);
        }

        $log = new ActivityLog();
        $log->log('permissions.synced', 'permission', null, 'Permissões sincronizadas');

        Session::flash('success', 'Permissões atualizadas com sucesso!');
        $this->redirect('/admin/permissions');
    }
}
