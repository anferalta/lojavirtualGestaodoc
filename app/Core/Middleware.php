<?php

namespace App\Core;

class Middleware
{
    public static function run(array $middlewares): void
    {
        foreach ($middlewares as $mw) {

            // Middleware com parâmetro (ex: "acl:documentos.ver")
            if (str_contains($mw, ':')) {
                [$name, $param] = explode(':', $mw, 2);
            } else {
                $name = $mw;
                $param = null;
            }

            // Nome da classe (ex: App\Middleware\AuthMiddleware)
            $class = "App\\Middleware\\" . ucfirst($name) . "Middleware";

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