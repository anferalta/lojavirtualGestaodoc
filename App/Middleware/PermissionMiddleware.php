<?php

namespace App\Middlewares;

use App\Core\Acl;
use App\Controllers\ErrorController;

class PermissaoMiddleware
{
    public static function handle(string $requiredPermission): void
    {
        if (!Acl::can($requiredPermission)) {
            (new ErrorController())->error403();
            exit;
        }
    }
}