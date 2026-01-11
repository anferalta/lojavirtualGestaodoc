<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Sessao;

class AuthController extends BaseController
{
    public function loginForm()
    {
        return $this->view('auth/login');
    }

    public function login()
    {
        error_log("POST RECEBIDO: " . json_encode($_POST));
        // Verifica CSRF
        if (!Sessao::checkCsrf($_POST['_csrf'] ?? '')) {
            die("CSRF inválido");
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (Auth::attempt($email, $password)) {
            Sessao::flash('success', 'Login efetuado com sucesso');
            return Helpers::redirect('/dashboard');
        }

        Sessao::flash('error', 'Credenciais inválidas');
        return Helpers::redirect('/login');
    }

    public function logout()
    {
        Auth::logout();
        return Helpers::redirect('/login');
    }
}