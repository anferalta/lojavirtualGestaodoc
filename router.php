<?php

use app\Core\Auth;
use app\Core\Helpers;
use app\Middlewares\PermissaoMiddleware;

$router = new AltoRouter();
$router->setBasePath('/');

/*
|--------------------------------------------------------------------------
| Rotas
|--------------------------------------------------------------------------
*/

$router->map('GET', '/', [
    'controller' => 'DashboardController',
    'method'     => 'index',
    'middlewares'=> ['auth']
], 'dashboard');

/* Utilizadores */
$router->map('GET', '/utilizadores', [
    'controller' => 'UtilizadoresController',
    'method'     => 'index',
    'middlewares'=> ['auth', 'perm:utilizadores.ver']
], 'utilizadores_index');

/* Perfis */
$router->map('GET', '/perfis', [
    'controller' => 'PerfisController',
    'method'     => 'index',
    'middlewares'=> ['auth', 'perm:perfis.ver']
], 'perfis_index');

/* Permissões */
$router->map('GET', '/permissoes', [
    'controller' => 'PermissoesController',
    'method'     => 'index',
    'middlewares'=> ['auth', 'perm:permissoes.ver']
], 'permissoes_index');

/*
|--------------------------------------------------------------------------
| Dispatcher
|--------------------------------------------------------------------------
*/

$match = $router->match();

if (!$match) {
    (new App\Controllers\ErrorController())->error404();
    exit;
}

$target = $match['target'];

$controllerName = $target['controller'];
$methodName     = $target['method'];
$middlewares    = $target['middlewares'] ?? [];

/*
|--------------------------------------------------------------------------
| Middlewares
|--------------------------------------------------------------------------
*/

foreach ($middlewares as $mw) {

    if ($mw === 'auth' && !Auth::check()) {
        Helpers::redirecionar('/login');
        exit;
    }

    if (str_starts_with($mw, 'perm:')) {
        $perm = substr($mw, 5);
        PermissaoMiddleware::handle($perm);
    }
}

/*
|--------------------------------------------------------------------------
| Executar Controller
|--------------------------------------------------------------------------
*/

$controllerClass = "App\\Controllers\\$controllerName";
$controller = new $controllerClass();

call_user_func_array([$controller, $methodName], $match['params']);