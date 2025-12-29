<?php
namespace App\Core;

class Route
{
    private static array $routes = [];

    public static function addRoute(string $method, string $path, string $action, array $middlewares = []): void
    {
        self::$routes[] = [
            'method'      => strtoupper($method),
            'path'        => $path,
            'action'      => $action,
            'middlewares' => $middlewares
        ];
    }

    public static function get(string $path, string $action, array $middlewares = []): void
    {
        self::addRoute('GET', $path, $action, $middlewares);
    }

    public static function post(string $path, string $action, array $middlewares = []): void
    {
        self::addRoute('POST', $path, $action, $middlewares);
    }

    public static function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach (self::$routes as $route) {

            if ($route['method'] === $method && $route['path'] === $uri) {

                // Executar middlewares
                if (!empty($route['middlewares'])) {
                    Middleware::run($route['middlewares']);
                }

                // Chamar controller
                [$controller, $method] = explode('@', $route['action']);
                $controller = "App\\Controllers\\{$controller}";

                (new $controller())->$method();
                return;
            }
        }

        // 404
        (new \App\Controllers\ErrorController())->notFound();
    }
}