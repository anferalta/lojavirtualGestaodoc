<?php

namespace App\Core;

class Route {

    private static array $routes = [];
    private static array $groupStack = [];

    /*
      |--------------------------------------------------------------------------
      | Métodos HTTP
      |--------------------------------------------------------------------------
     */

    public static function get(string $uri, string $action, array $middlewares = []): void {
        self::addRoute('GET', $uri, $action, $middlewares);
    }

    public static function post(string $uri, string $action, array $middlewares = []): void {
        self::addRoute('POST', $uri, $action, $middlewares);
    }

    /*
      |--------------------------------------------------------------------------
      | Grupos de rotas
      |--------------------------------------------------------------------------
     */

    public static function group(array $attributes, callable $callback): void {
        self::$groupStack[] = $attributes;
        $callback();
        array_pop(self::$groupStack);
    }

    /*
      |--------------------------------------------------------------------------
      | Registar rota
      |--------------------------------------------------------------------------
     */

    private static function addRoute(string $method, string $uri, string $action, array $middlewares): void {
        $uri = '/' . trim($uri, '/');

        $prefix = '';
        $groupMiddlewares = [];

        foreach (self::$groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $groupMiddlewares = array_merge($groupMiddlewares, $group['middleware']);
            }
        }

        $uri = $prefix . $uri;

        self::$routes[$method][] = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => array_merge($groupMiddlewares, $middlewares)
        ];
    }

    /*
      |--------------------------------------------------------------------------
      | Dispatcher
      |--------------------------------------------------------------------------
     */

    public static function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $uri = '/' . trim($uri, '/');

        if (!isset(self::$routes[$method])) {
            self::abort404();
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

                if (!class_exists($controller)) {
                    throw new \Exception("Controller $controller não encontrado.");
                }

                $instance = new $controller();

                if (!method_exists($instance, $action)) {
                    throw new \Exception("Método $action não existe no controller $controller.");
                }

                $instance->$action(...$matches);
                return;
            }
        }

        self::abort404();
    }

    private static function abort404(): void {
        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1>";
        exit;
    }
}
