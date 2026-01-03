<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public function handle()
    {
        // Se não estiver autenticado, redireciona para login
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
    }
}