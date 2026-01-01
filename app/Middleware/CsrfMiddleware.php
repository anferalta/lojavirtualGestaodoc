<?php

namespace App\Middleware;

use App\Core\Sessao;

class CsrfMiddleware
{
    public static function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $token = $_POST['_csrf'] ?? null;

            if (!$token || !Sessao::validarCsrf($token)) {
                http_response_code(419);
                die('CSRF token inválido.');
            }
        }
    }
}