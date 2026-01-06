<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Sessao;
use App\Core\Helpers;

class AuthController extends BaseController
{
    /**
     * Formulário de login
     */
    public function loginForm()
    {
        return $this->view('auth/login', [
            'csrf' => Sessao::csrf(),
            'flash' => Sessao::getFlash()
        ]);
    }

    /**
     * Submissão do login
     */
    public function login()
    {
        // Proteção CSRF
        if (!Sessao::checkCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('erro', 'Token CSRF inválido.');
            return $this->redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$email || !$password) {
            Sessao::flash('erro', 'Preencha todos os campos.');
            return $this->redirect('/login');
        }

        // Autenticação
        $user = Auth::attempt($email, $password);

        if (!$user) {
            Sessao::flash('erro', 'Credenciais inválidas.');
            return $this->redirect('/login');
        }

        // Se o utilizador tiver 2FA ativo
        if ($user->two_factor_enabled) {
            Sessao::set('2fa_user', $user->id);
            return $this->redirect('/2fa');
        }

        // Login concluído
        Auth::login($user);
        Sessao::flash('sucesso', 'Bem-vindo de volta, ' . $user->nome . '!');

        return $this->redirect('/dashboard');
    }

    /**
     * Formulário de 2FA
     */
    public function twoFactorForm()
    {
        if (!Sessao::has('2fa_user')) {
            return $this->redirect('/login');
        }

        return $this->view('auth/2fa', [
            'csrf' => Sessao::csrf(),
            'flash' => Sessao::getFlash()
        ]);
    }

    /**
     * Validação do código 2FA
     */
    public function twoFactorValidate()
    {
        if (!Sessao::checkCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('erro', 'Token CSRF inválido.');
            return $this->redirect('/2fa');
        }

        $code = trim($_POST['code'] ?? '');

        if (!$code) {
            Sessao::flash('erro', 'Introduza o código.');
            return $this->redirect('/2fa');
        }

        $userId = Sessao::get('2fa_user');
        $user = Auth::find($userId);

        if (!$user || !Auth::validate2FA($user, $code)) {
            Sessao::flash('erro', 'Código inválido.');
            return $this->redirect('/2fa');
        }

        Sessao::forget('2fa_user');
        Auth::login($user);

        Sessao::flash('sucesso', 'Autenticação concluída.');
        return $this->redirect('/dashboard');
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::logout();
        Sessao::flash('sucesso', 'Sessão terminada.');
        return $this->redirect('/login');
    }
}