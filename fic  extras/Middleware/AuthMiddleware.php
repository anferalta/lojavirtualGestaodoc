<?php
namespace app\Middleware;

use app\Core\Auth;
use app\Core\Helpers;
use app\Core\Sessao;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            Sessao::setFlash('É necessário iniciar sessão.', 'warning');
            Helpers::redirecionar('/login');
        }
    }
}