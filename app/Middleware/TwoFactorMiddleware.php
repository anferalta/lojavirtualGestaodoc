<?php

namespace App\Middleware;

use App\Core\Auth;

class TwoFactorMiddleware
{
    public function handle()
    {
        // Se não estiver autenticado, não faz nada
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Se o utilizador não tem 2FA ativo, segue
        if (!$user->two_factor_ativo) {
            return;
        }

        // Se já validou 2FA nesta sessão, segue
        if (!empty($_SESSION['2fa_validado'])) {
            return;
        }

        // Redireciona para validação
        header('Location: /2fa/validar');
        exit;
    }
}