<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Sessao;
use App\Core\Acl;

class AuthController extends BaseController {

    public function loginForm(): void {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login');
    }

    public function login(): void {
        
        // Validar CSRF
        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validarCsrf($token)) {
            Sessao::flash('Sessão expirada. Por favor tente novamente.', 'error');
            $this->redirect('/login');
        }

        // Normalizar inputs
        $email = strtolower(trim($_POST['email'] ?? ''));
        $senha = trim($_POST['senha'] ?? '');

        if ($email === '' || $senha === '') {
            Sessao::flash('Preencha todos os campos.', 'warning');
            $this->redirect('/login');
        }

        // Autenticação
        if (!Auth::attempt($email, $senha)) {
            Sessao::flash('Credenciais inválidas.', 'error');
            $this->redirect('/login');
        }

        // Limpar cache ACL
        Acl::flush();

        // Garantir que a sessão é gravada
        session_write_close();

        // Redirecionar
        Sessao::flash('Bem-vindo de volta!', 'success');
        $this->redirect('/dashboard');
    }

    public function logout() {
        Acl::flush();      // ← limpa o cache do ACL
        session_destroy(); // ← limpa a sessão
        header("Location: /login");
        exit;
    }
}
