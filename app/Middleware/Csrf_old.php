<?php

namespace App\Middleware;

use App\Core\Sessao;

class Csrf
{
    public function handle()
    {
        Sessao::start();

        // 1. Se não existir token, cria UM e mantém sempre o mesmo
        if (!Sessao::tem('csrf_token')) {
            Sessao::gravar('csrf_token', bin2hex(random_bytes(32)));
        }

        $tokenSessao = Sessao::obter('csrf_token');

        // 2. Validar apenas POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $tokenEnviado = $_POST['_csrf'] ?? null;

            if (!$tokenEnviado || $tokenEnviado !== $tokenSessao) {
                http_response_code(403);
                die('CSRF token inválido');
            }
        }

        return true;
    }

    public static function token()
    {
        Sessao::start();
        return Sessao::obter('csrf_token');
    }
}