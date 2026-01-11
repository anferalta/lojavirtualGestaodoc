<?php

namespace App\Core;

class Middleware {

    private static array $aliases = [
        'auth' => 'AuthMiddleware',
        'csrf' => 'CsrfMiddleware',
        'permission' => 'PermissionMiddleware',
        'twofactor' => 'TwoFactorMiddleware',
        '2fa' => 'TwoFactorMiddleware',
    ];

    public static function run(array $middlewares): void {
        foreach ($middlewares as $mw) {

            // Middleware com parâmetro (ex: "acl:documentos.ver")
            if (str_contains($mw, ':')) {
                [$name, $param] = explode(':', $mw, 2);
            } else {
                $name = $mw;
                $param = null;
            }

            // Aplicar alias corretamente
            $name = self::$aliases[$name] ?? $name;

            // Nome da classe final
            $class = "App\\Middleware\\{$name}";

            if (!class_exists($class)) {
                throw new \Exception("Middleware '$class' não encontrado.");
            }

            $instance = new $class();

            if ($param !== null) {
                $instance->handle($param);
            } else {
                $instance->handle();
            }
        }
    }
}