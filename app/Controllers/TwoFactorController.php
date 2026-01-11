<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Helpers;
use App\Core\Sessao;
use App\Core\BaseController;

class TwoFactorController extends BaseController
{
    public function validarForm(): void
    {
        $this->view('auth/2fa_validar', [
            'csrf' => Sessao::csrf()
        ]);
    }

    public function validar(): void
    {
        Sessao::start();

        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validarCsrf($token)) {
            Sessao::flash('error', 'Token CSRF inválido');
            Helpers::redirect('/2fa/validar');
        }

        $codigo = trim($_POST['codigo'] ?? '');

        if (!Auth::validar2fa($codigo)) {
            Sessao::flash('error', 'Código 2FA inválido');
            Helpers::redirect('/2fa/validar');
        }

        Auth::marcar2faComoValidado();

        Sessao::flash('success', 'Autenticação 2FA validada com sucesso');
        Helpers::redirect('/dashboard');
    }
}