<?php
namespace App\Core;

class Route {

    private static array $routes = [];

    public static function get(string $uri, string $action, array $middlewares = []): void {
        self::$routes['GET'][] = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public static function post(string $uri, string $action, array $middlewares = []): void {
        self::$routes['POST'][] = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public static function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        if (!isset(self::$routes[$method])) {
            http_response_code(404);
            echo "404 - Página não encontrada";
            return;
        }

        foreach (self::$routes[$method] as $route) {

            $pattern = preg_replace('#\{([^}]+)\}#', '([^/]+)', $route['uri']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {

                array_shift($matches);

                if (!empty($route['middlewares'])) {
                    Middleware::run($route['middlewares']);
                }

                [$controller, $action] = explode('@', $route['action']);
                $controller = "App\\Controllers\\$controller";

                $instance = new $controller();
                $instance->$action(...$matches);

                return;
            }
        }

        http_response_code(404);
        echo "404 - Página não encontrada";
    }
}
    