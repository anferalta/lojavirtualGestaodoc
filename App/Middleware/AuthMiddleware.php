<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Helpers;
use App\Core\Sessao;

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