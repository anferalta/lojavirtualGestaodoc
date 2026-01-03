<?php

use App\Core\Middleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\TwoFactorMiddleware;

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