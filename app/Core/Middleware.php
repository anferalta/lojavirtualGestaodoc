<?php

namespace App\Core;

class Middleware
{
    private static array $middlewares = [];

    public static function register(string $name, callable $callback): void
    {
        self::$middlewares[$name] = $callback;
    }

    public static function run(array $middlewares): void
    {
        foreach ($middlewares as $mw) {

            if (str_contains($mw, ':')) {
                [$name, $param] = explode(':', $mw, 2);

                if (!isset(self::$middlewares[$name])) {
                    throw new \Exception("Middleware '$name' não registado.");
                }

                call_user_func(self::$middlewares[$name], $param);
                continue;
            }

            if (!isset(self::$middlewares[$mw])) {
                throw new \Exception("Middleware '$mw' não registado.");
            }

            call_user_func(self::$middlewares[$mw]);
        }
    }
}