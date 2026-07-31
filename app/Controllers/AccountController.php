<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Models\ActivityLog;

/**
 * Conta do usuário logado: alteração da própria senha.
 */
class AccountController extends Controller
{
    /**
     * Formulário de alteração de senha.
     */
    public function password(): void
    {
        $this->requireAuth();
        $this->view('account/password');
    }

    /**
     * Processa a troca de senha (exige a senha atual).
     */
    public function updatePassword(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/account/password');
            return;
        }

        $current = (string) $this->input('current_password', '');
        $new     = (string) $this->input('new_password', '');
        $confirm = (string) $this->input('confirm_password', '');

        if ($current === '' || $new === '' || $confirm === '') {
            Session::flash('error', 'Preencha todos os campos.');
            $this->redirect('/account/password');
            return;
        }

        // Confirma a senha atual do usuário logado
        $userModel = new User();
        $user = $userModel->find(Auth::userId());
        if (!$user || !password_verify($current, $user->password)) {
            Session::flash('error', 'A senha atual está incorreta.');
            $this->redirect('/account/password');
            return;
        }

        if (strlen($new) < 6) {
            Session::flash('error', 'A nova senha deve ter pelo menos 6 caracteres.');
            $this->redirect('/account/password');
            return;
        }

        if ($new !== $confirm) {
            Session::flash('error', 'A confirmação não corresponde à nova senha.');
            $this->redirect('/account/password');
            return;
        }

        if (password_verify($new, $user->password)) {
            Session::flash('error', 'A nova senha deve ser diferente da atual.');
            $this->redirect('/account/password');
            return;
        }

        $userModel->updateUser((int) $user->id, ['password' => $new]);

        (new ActivityLog())->log('user.password_changed', 'user', (int) $user->id, 'Senha alterada pelo próprio usuário');

        Session::flash('success', 'Senha alterada com sucesso!');
        $this->redirect('/account/password');
    }
}
