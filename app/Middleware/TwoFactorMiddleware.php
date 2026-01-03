<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Sessao;

class TwoFactorMiddleware
{
    public function handle()
    {
        $user = Auth::user();

        // Se não estiver autenticado, não faz sentido validar 2FA
        if (!$user) {
            return;
        }

        // Se o utilizador não tem 2FA ativo, segue
        if (!$user->two_factor_ativo) {
            return;
        }

        // Se já validou 2FA nesta sessão, segue
        if (!empty($_SESSION['2fa_validado'])) {
            return;
        }

        // Caso contrário, redireciona para validação
        header('Location: /2fa/validar');
        exit;
    }
}