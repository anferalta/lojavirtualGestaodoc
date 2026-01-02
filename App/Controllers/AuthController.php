<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Sessao;

class AuthController extends BaseController
{
    /**
     * Mostrar formulário de login
     */
    public function loginForm(): void
    {
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
        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validarCsrf($token)) {
            Sessao::flash('Sessão expirada. Por favor tente novamente.', 'error');
            $this->redirect('login');
        }

        // Normalizar inputs
        $email = strtolower(trim($_POST['email'] ?? ''));
        $senha = trim($_POST['senha'] ?? '');

        // Validação
        if ($email === '' || $senha === '') {
            Sessao::flash('Preencha todos os campos.', 'warning');
            $this->redirect('login');
        }

        // Autenticação
        if (!Auth::attempt($email, $senha)) {
            Sessao::flash('Credenciais inválidas.', 'error');
            $this->redirect('login');
        }

        // Sucesso
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