<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\View;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        // Se já está logado, redireciona pro dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
            return;
        }

        View::render('auth/login', [], 'auth');
    }

    public function login(): void
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de segurança inválido. Tente novamente.');
            $this->redirect('/login');
            return;
        }

        $email    = trim($this->input('email', ''));
        $password = $this->input('password', '');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Preencha todos os campos.');
            $this->redirect('/login');
            return;
        }

        if (Auth::attempt($email, $password)) {
            $log = new ActivityLog();
            $log->log('login', 'user', Auth::userId(), 'Login realizado com sucesso');

            Session::flash('success', 'Bem-vindo(a), ' . Auth::userName() . '!');
            $this->redirect('/dashboard');
        } else {
            Session::flash('error', 'Email ou senha inválidos.');
            $this->redirect('/login');
        }
    }

    public function logout(): void
    {
        if (Auth::check()) {
            $log = new ActivityLog();
            $log->log('logout', 'user', Auth::userId(), 'Logout realizado');
        }

        Auth::logout();
        Session::flash('success', 'Você saiu do sistema.');
        $this->redirect('/login');
    }
}
