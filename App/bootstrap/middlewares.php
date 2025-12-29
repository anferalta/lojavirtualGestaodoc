<?php

use App\Core\Middleware;
use App\Core\Sessao;
use App\Core\Helpers;
use App\Middleware\TwoFactorMiddleware;

/*
|--------------------------------------------------------------------------
| Middleware: Autenticação
|--------------------------------------------------------------------------
*/
Middleware::register('auth', function () {
    if (!Sessao::tem('user_id')) {
        Sessao::setFlash('É necessário iniciar sessão para aceder a esta área.', 'danger');
        Helpers::redirecionar('/login');
        exit;
    }
});

/*
|--------------------------------------------------------------------------
| Middleware: 2FA  ← ADICIONAR ISTO
|--------------------------------------------------------------------------
*/
Middleware::register('2fa', function () {
    TwoFactorMiddleware::handle();
});

/*
|--------------------------------------------------------------------------
| Middleware: Permissões (ACL)
|--------------------------------------------------------------------------
*/
Middleware::register('perm', function ($permissao) {
    if (!Helpers::can($permissao)) {
        Sessao::setFlash('Não tem permissão para aceder a esta área.', 'danger');
        Helpers::redirecionar('/403');
        exit;
    }
});

/*
|--------------------------------------------------------------------------
| Middleware: CSRF
|--------------------------------------------------------------------------
*/
Middleware::register('csrf', function () {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!isset($_POST['_csrf']) || !Sessao::tem('_csrf')) {
            http_response_code(403);
            die('Token CSRF em falta.');
        }

        if ($_POST['_csrf'] !== Sessao::get('_csrf')) {
            http_response_code(403);
            die('Token CSRF inválido.');
        }
    }
});