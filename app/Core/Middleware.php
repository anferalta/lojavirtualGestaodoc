<?php

namespace App\Core;

class Middleware {

    /**
     * Mapa de aliases → classes reais
     */
    private static array $map = [
        'auth' => \App\Middleware\AuthMiddleware::class,
        'acl' => \App\Middleware\AclMiddleware::class,
        'csrf' => \App\Middleware\CsrfMiddleware::class,
        'TwoFactorMiddleware' => \App\Middleware\TwoFactorMiddleware::class,
    ];

    /**
     * Executa uma lista de middlewares
     */
    public static function run(array $middlewares): void {
        foreach ($middlewares as $middleware) {

            // Separar nome e parâmetro (ex: "acl:utilizadores.ver")
            $parts = explode(':', $middleware);
            $name = $parts[0];
            $param = $parts[1] ?? null;

            // Resolver classe do middleware
            $class = self::resolveMiddlewareClass($name);

            if (!class_exists($class)) {
                throw new \Exception("Middleware '$class' não encontrado.");
            }

            $instance = new $class();

            // Executar middleware com ou sem parâmetro
            if ($param !== null) {
                $instance->handle($param);
            } else {
                $instance->handle();
            }
        }
    }

    /**
     * Resolve o nome curto do middleware para a classe real
     */
    private static function resolveMiddlewareClass(string $name): string {
        // Se existir no mapa, usa o alias
        if (isset(self::$map[$name])) {
            return self::$map[$name];
        }

        // Caso contrário, tenta carregar diretamente pelo namespace
        return "App\\Middleware\\" . $name;
    }
}
