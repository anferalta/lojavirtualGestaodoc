<?php

namespace App\Middleware;

use App\Core\ACL;

class AclMiddleware
{
    public function handle($permission): void
    {
        if (!ACL::can($permission)) {
            header('Location: /403');
            exit;
        }
    }
}