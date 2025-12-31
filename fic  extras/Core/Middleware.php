<?php
namespace app\Core;

class Middleware
{
    private static array $middlewares = [];

    public static function register(string $nome, callable $callback): void
    {
        self::$middlewares[$nome] = $callback;
    }

    public static function run(array $lista): void
    {
        foreach ($lista as $mw) {

            // middleware com parâmetro: perm:utilizadores.ver
            if (str_contains($mw, ':')) {
                [$nome, $param] = explode(':', $mw, 2);
                if (isset(self::$middlewares[$nome])) {
                    call_user_func(self::$middlewares[$nome], $param);
                }
                continue;
            }

            // middleware simples
            if (isset(self::$middlewares[$mw])) {
                call_user_func(self::$middlewares[$mw]);
            }
        }
    }
}