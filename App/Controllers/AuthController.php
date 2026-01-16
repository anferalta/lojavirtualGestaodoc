<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\Helpers;
use App\Core\Sessao;

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        // O token já é injetado globalmente pelo BaseController
        $this->view('auth/login');
    }

    public function login(): void
    {
        Sessao::start();

        // Validar CSRF
        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validarCsrf($token)) {
            Sessao::flash('erro', 'Token CSRF inválido');
            Helpers::redirect('/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Sessao::flash('erro', 'Preencha todos os campos');
            Helpers::redirect('/login');
            return;
        }

        if (!Auth::attempt($email, $password)) {
            Sessao::flash('erro', 'Credenciais inválidas');
            Helpers::redirect('/login');
            return;
        }

        Sessao::flash('sucesso', 'Login efetuado com sucesso');

        // Regenerar token APÓS login
        Sessao::regenerateCsrf();

        Helpers::redirect('/painel');
    }

    public function logout(): void
    {
        Auth::logout();
        Sessao::flash('info', 'Sessão terminada');
        Helpers::redirect('/login');
    }
}