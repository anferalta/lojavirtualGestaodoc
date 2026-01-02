<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Sessao;
use App\Core\Helpers;

class AuthController extends BaseController
{
    /**
     * Mostrar formulário de login
     */
    public function loginForm(): void
    {
        // Se já está autenticado, vai para o dashboard
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/login');
    }

    /**
     * Processar login
     */
    public function login(): void
    {
        // Validar CSRF
        if (!Sessao::validarCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('Token CSRF inválido.', 'error');
            $this->redirect('login');
        }

        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        // Validação simples
        if ($email === '' || $senha === '') {
            Sessao::flash('Preencha todos os campos.', 'warning');
            $this->redirect('login');
        }

        // Tentar autenticar
        if (!Auth::attempt($email, $senha)) {
            Sessao::flash('Credenciais inválidas.', 'error');
            $this->redirect('login');
        }

        // Login OK
        Sessao::flash('Bem-vindo de volta!', 'success');
        $this->redirect('dashboard');
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        Auth::logout();
        Sessao::flash('Sessão terminada com sucesso.', 'info');
        $this->redirect('login');
    }
}