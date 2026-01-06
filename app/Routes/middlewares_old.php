<?php

use App\Core\Middleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\TwoFactorMiddleware;
use App\Core\ACL;
use App\Core\CSRF;

/*
|--------------------------------------------------------------------------
| Registo de Middlewares
|--------------------------------------------------------------------------
*/

Middleware::register('auth', function () {
    return new AuthMiddleware();
});

Middleware::register('2fa', function () {
    return new TwoFactorMiddleware();
});

/*
|--------------------------------------------------------------------------
| ACL (Permissões)
|--------------------------------------------------------------------------
*/
Middleware::register('acl', function ($permission) {
    if (!ACL::can($permission)) {
        header("Location: /403");
        exit;
    }
});

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/
Middleware::register('csrf', function () {
    if (!CSRF::validate()) {
        header("Location: /403");
        exit;
    }
});