<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;

class UserController extends Controller
{
    private User $userModel;
    private Role $roleModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    public function index(): void
    {
        $this->requirePermission('users.view');

        $users = $this->userModel->allWithRole();

        $this->view('users/index', [
            'users' => $users,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('users.create');

        $roles = $this->roleModel->all('name ASC');

        $this->view('users/create', [
            'roles' => $roles,
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('users.create');

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/users/create');
            return;
        }

        $data = [
            'name'    => trim($this->input('name', '')),
            'email'   => trim($this->input('email', '')),
            'phone'   => trim($this->input('phone', '')),
            'password'=> $this->input('password', ''),
            'role_id' => (int) $this->input('role_id', 3),
            'active'  => (int) $this->input('active', 1),
        ];

        // Validações
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            Session::flash('error', 'Nome, email e senha são obrigatórios.');
            $this->redirect('/users/create');
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Email inválido.');
            $this->redirect('/users/create');
            return;
        }

        if ($this->userModel->emailExists($data['email'])) {
            Session::flash('error', 'Este email já está cadastrado.');
            $this->redirect('/users/create');
            return;
        }

        if (strlen($data['password']) < 6) {
            Session::flash('error', 'A senha deve ter pelo menos 6 caracteres.');
            $this->redirect('/users/create');
            return;
        }

        $id = $this->userModel->createUser($data);

        $log = new ActivityLog();
        $log->log('user.created', 'user', $id, "Usuário {$data['name']} criado");

        Session::flash('success', 'Usuário criado com sucesso!');
        $this->redirect('/users');
    }

    public function edit(string $id): void
    {
        $this->requirePermission('users.edit');

        $user = $this->userModel->find((int) $id);
        if (!$user) {
            Session::flash('error', 'Usuário não encontrado.');
            $this->redirect('/users');
            return;
        }

        $roles = $this->roleModel->all('name ASC');

        $this->view('users/edit', [
            'user'  => $user,
            'roles' => $roles,
        ]);
    }

    public function update(string $id): void
    {
        $this->requirePermission('users.edit');

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect("/users/{$id}/edit");
            return;
        }

        $user = $this->userModel->find((int) $id);
        if (!$user) {
            Session::flash('error', 'Usuário não encontrado.');
            $this->redirect('/users');
            return;
        }

        $data = [
            'name'    => trim($this->input('name', '')),
            'email'   => trim($this->input('email', '')),
            'phone'   => trim($this->input('phone', '')),
            'role_id' => (int) $this->input('role_id', $user->role_id),
            'active'  => (int) $this->input('active', 0),
        ];

        $password = $this->input('password', '');
        if (!empty($password)) {
            if (strlen($password) < 6) {
                Session::flash('error', 'A senha deve ter pelo menos 6 caracteres.');
                $this->redirect("/users/{$id}/edit");
                return;
            }
            $data['password'] = $password;
        }

        if (empty($data['name']) || empty($data['email'])) {
            Session::flash('error', 'Nome e email são obrigatórios.');
            $this->redirect("/users/{$id}/edit");
            return;
        }

        if ($this->userModel->emailExists($data['email'], (int) $id)) {
            Session::flash('error', 'Este email já está cadastrado por outro usuário.');
            $this->redirect("/users/{$id}/edit");
            return;
        }

        $this->userModel->updateUser((int) $id, $data);

        $log = new ActivityLog();
        $log->log('user.updated', 'user', (int) $id, "Usuário {$data['name']} atualizado");

        Session::flash('success', 'Usuário atualizado com sucesso!');
        $this->redirect('/users');
    }

    public function delete(string $id): void
    {
        $this->requirePermission('users.delete');

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/users');
            return;
        }

        $user = $this->userModel->find((int) $id);
        if (!$user) {
            Session::flash('error', 'Usuário não encontrado.');
            $this->redirect('/users');
            return;
        }

        // Não permite excluir o próprio superadmin
        if ((int) $user->role_id === 1) {
            Session::flash('error', 'Não é possível excluir um superadmin.');
            $this->redirect('/users');
            return;
        }

        $this->userModel->delete((int) $id);

        $log = new ActivityLog();
        $log->log('user.deleted', 'user', (int) $id, "Usuário {$user->name} excluído");

        Session::flash('success', 'Usuário excluído com sucesso!');
        $this->redirect('/users');
    }
}
