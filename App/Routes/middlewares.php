<?php

use App\Core\Middleware;
use App\Core\Permission;
use App\Core\Conexao;

/**
 * Middleware de autenticação
 */
Middleware::register('auth', function () {
    \App\Core\Auth::verificarLogin();
});

/**
 * Middleware de permissões (ACL)
 */
Middleware::register('perm', function ($permissionName) {
    $perm = new Permission(Conexao::getInstancia());

    if (!$perm->userHas($_SESSION['user_id'], $permissionName)) {
        http_response_code(403);
        $err = new \App\Controllers\ErrorController();
        $err->error403();
        exit;
    }
});