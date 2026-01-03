<?php

use App\Core\Auth;
use App\Core\Sessao;
use App\Core\Helpers;
use App\Core\Acl;
use App\Core\Middleware;

/**
 * Middleware de autenticação
 */
Middleware::register('auth', function () {
    if (!Auth::check()) {
        Sessao::flash('É necessário autenticação.', 'danger');
        header('Location: /login');
        exit;
    }
});

/**
 * Middleware de permissões
 */
Middleware::register('perm', function ($permissionKey) {

    if (!Auth::check()) {
        Sessao::flash('É necessário autenticação.', 'danger');
        header('Location: /login');
        exit;
    }

    if (!Acl::can($permissionKey)) {
        http_response_code(403);
        Sessao::flash('Não tem permissão para aceder a esta área.', 'danger');
        header('Location: /painel');
        exit;
    }
});