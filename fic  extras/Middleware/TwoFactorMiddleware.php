<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Helpers;

class TwoFactorMiddleware
{
    public static function handle(): void
    {
        $user = Auth::user();

        // Se não houver utilizador autenticado, não faz nada
        if (!$user) {
            return;
        }

        // Se o 2FA estiver ativo e ainda não validado
        if ($user->two_factor_ativo == 1 && !isset($_SESSION['2fa_validado'])) {
            Helpers::redirecionar('/2fa/validar');
            exit; // ← ESSENCIAL
        }
    }
}