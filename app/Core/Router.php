<?php

namespace App\Core;

use Exception;
use ReflectionClass;

class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function get(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $uri, $action, $middlewares);
    }

    public function post(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('POST', $uri, $action, $middlewares);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback();
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $uri, string $action, array $middlewares): void
    {
        $uri = '/' . trim($uri, '/');

        $prefix = '';
        $groupMiddlewares = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $groupMiddlewares = array_merge($groupMiddlewares, $group['middleware']);
            }
        }

        //$uri = $prefix . $uri;
        $uri = rtrim($prefix . $uri, '/');
if ($uri === '') $uri = '/';

        $this->routes[$method][] = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => array_merge($groupMiddlewares, $middlewares)
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        //$uri = '/' . trim($uri, '/');
        $uri = '/' . trim($uri, '/');
if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}

        if (!isset($this->routes[$method])) {
            $this->abort404();
        }

        foreach ($this->routes[$method] as $route) {

            $pattern = preg_replace('#\{([^}]+)\}#', '([^/]+)', $route['uri']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {

                array_shift($matches);

                if (!empty($route['middlewares'])) {
                    Middleware::run($route['middlewares']);
                }

                [$controller, $action] = explode('@', $route['action']);

                $controller = $this->resolveController($controller);

                if (!class_exists($controller)) {
                    throw new Exception("Controller $controller não encontrado.");
                }

                $instance = new $controller();

                if (!method_exists($instance, $action)) {
                    throw new Exception("Método $action não existe no controller $controller.");
                }

                $instance->$action(...$matches);
                return;
            }
        }

        $this->abort404();
    }

    private function resolveController(string $controller): string
    {
        // Normalizar barras
        $controller = str_replace('/', '\\', $controller);

        // Se já contém namespace, prefixar com App\Controllers\
        if (str_contains($controller, '\\')) {
            return "App\\Controllers\\" . $controller;
        }

        // Caso contrário, assume raiz
        return "App\\Controllers\\" . $controller;
    }

    private function abort404(): void
    {
        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1>";
        exit;
    }
}