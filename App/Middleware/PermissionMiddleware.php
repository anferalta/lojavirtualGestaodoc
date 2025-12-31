<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Acl;
use App\Core\Helpers;
use App\Core\Sessao;

class PermissionMiddleware
{
    /**
     * Verifica permissão antes de entrar numa rota/controlador.
     */
    public static function handle(string $permissionKey): void
    {
        if (!Auth::check()) {
            Sessao::flash('É necessário autenticação.', 'danger');
            Helpers::redirecionar('/login');
            exit;
        }

        if (!Acl::can($permissionKey)) {
            Sessao::flash('Não tem permissão para aceder a esta área.', 'danger');
            Helpers::redirecionar('/painel');
            exit;
        }
    }
}