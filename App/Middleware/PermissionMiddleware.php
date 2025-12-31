<?php

namespace app\Middlewares;

use app\Core\Acl;
use app\Controllers\ErrorController;

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