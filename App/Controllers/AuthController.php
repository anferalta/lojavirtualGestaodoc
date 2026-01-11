<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\Helpers;
use App\Core\Sessao;

class AuthController extends BaseController {

    public function showLogin(): void {
        $data = [
            'csrf' => Sessao::csrf(),
        ];

        $this->view('auth/login', $data);
    }

    public function loginForm(): void {
        // Se já estiver autenticado, redireciona para o dashboard
        if (\App\Core\Auth::check()) {
            \App\Core\Helpers::redirect('/dashboard');
        }

        $data = [
            'csrf' => \App\Core\Sessao::csrf(),
        ];

        $this->view('auth/login', $data);
    }

    public function login(): void {
        Sessao::start();

        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validarCsrf($token)) {
            Sessao::flash('error', 'Token CSRF inválido');
            Helpers::redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Sessao::flash('error', 'Preencha todos os campos');
            Helpers::redirect('/login');
        }

        if (!Auth::attempt($email, $password)) {
            Sessao::flash('error', 'Credenciais inválidas');
            Helpers::redirect('/login');
        }

        Sessao::flash('success', 'Login efetuado com sucesso');
        Sessao::regenerateCsrf();

        Helpers::redirect('/dashboard');
    }

    public function logout(): void {
        Auth::logout();
        Sessao::flash('info', 'Sessão terminada');
        Helpers::redirect('/login');
    }
}
