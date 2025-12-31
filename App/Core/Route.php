<?php

namespace app\Core;

class Route
{
    private static array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => []
    ];

    private static array $globalMiddlewares = [];

    /**
     * Registrar middlewares globais
     */
    public static function middleware(array $middlewares): void
    {
        self::$globalMiddlewares = array_merge(self::$globalMiddlewares, $middlewares);
    }

    /**
     * Registrar rotas GET
     */
    public static function get(string $uri, string $action, array $middlewares = []): void
    {
        self::addRoute('GET', $uri, $action, $middlewares);
    }

    /**
     * Registrar rotas POST
     */
    public static function post(string $uri, string $action, array $middlewares = []): void
    {
        self::addRoute('POST', $uri, $action, $middlewares);
    }

    /**
     * Registrar rotas genéricas
     */
    private static function addRoute(string $method, string $uri, string $action, array $middlewares): void
    {
        $uri = rtrim($uri, '/') ?: '/';

        self::$routes[$method][] = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Despachar rota
     */
    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $uri = rtrim($uri, '/') ?: '/';

        if (!isset(self::$routes[$method])) {
            return self::abort404();
        }

        foreach (self::$routes[$method] as $route) {

            $pattern = preg_replace('#\{([^}]+)\}#', '([^/]+)', $route['uri']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {

                array_shift($matches);

                // Middlewares globais
                if (!empty(self::$globalMiddlewares)) {
                    Middleware::run(self::$globalMiddlewares);
                }

                // Middlewares da rota
                if (!empty($route['middlewares'])) {
                    Middleware::run($route['middlewares']);
                }

                [$controller, $action] = explode('@', $route['action']);
                $controller = "app\\Controllers\\$controller";

                if (!class_exists($controller)) {
                    throw new \Exception("Controller $controller não encontrado.");
                }

                $instance = new $controller();

                if (!method_exists($instance, $action)) {
                    throw new \Exception("Método $action não existe no controller $controller.");
                }

                return $instance->$action(...$matches);
            }
        }

        return self::abort404();
    }

    /**
     * Página 404 customizada
     */
    private static function abort404(): void
    {
        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1>";
        exit;
    }
}