<?php

namespace App\Middleware;

use App\Core\Sessao;
use App\Models\Usuario;

class TwoFactorMiddleware
{
    public static function handle()
    {
        // 1. Se não está autenticado, não faz nada
        if (!Sessao::tem('usuario_id')) {
            return;
        }

        // 2. Carregar utilizador
        $usuario = Usuario::find(Sessao::get('usuario_id'));

        // 3. Se o 2FA não está ativo, não faz nada
        if (!$usuario->two_factor_ativo) {
            return;
        }

        // 4. Se já validou o 2FA nesta sessão, não faz nada
        if (Sessao::tem('2fa_validado')) {
            return;
        }

        // 5. Se está na página de validação, deixa passar
        $current = strtok($_SERVER['REQUEST_URI'], '?');
        if ($current === '/2fa/validar') {
            return;
        }

        // 6. Caso contrário, redireciona para validar
        redirecionar('/2fa/validar');
    }
}