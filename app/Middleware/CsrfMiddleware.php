<?php

namespace App\Middleware;

use App\Core\Sessao;

class CsrfMiddleware
{
    public function handle()
    {
        Sessao::start();

        // Garante que o token existe
        Sessao::csrf();

        // Validar apenas POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $tokenEnviado = $_POST['_csrf'] ?? '';

            if (!Sessao::validarCsrf($tokenEnviado)) {
                http_response_code(403);
                die('Token CSRF inválido');
            }
        }

        return true;
    }
}