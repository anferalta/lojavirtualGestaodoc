<?php

use App\Core\Auth;
use App\Core\Helpers;
use App\Middlewares\PermissaoMiddleware;

$router = new AltoRouter();

/*
|--------------------------------------------------------------------------
| Base Path
|--------------------------------------------------------------------------
*/
$router->setBasePath('');


/*
|--------------------------------------------------------------------------
| Rotas
|--------------------------------------------------------------------------
*/

// Home → redireciona para login
$router->map('GET', '/', function () {
    header('Location: /login');
    exit;
}, 'home');

/* Dashboard */
$router->map('GET', '/dashboard', [
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

/* Login */
$router->map('GET', '/login', [
    'controller' => 'AuthController',
    'method'     => 'loginForm'
], 'login_form');

$router->map('POST', '/login', [
    'controller' => 'AuthController',
    'method'     => 'login'
], 'login_submit');

/* Logout */
$router->map('GET', '/logout', [
    'controller' => 'AuthController',
    'method'     => 'logout',
    'middlewares'=> ['auth']
], 'logout');


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

/*
|--------------------------------------------------------------------------
| 1. Se a rota for uma Closure, executa diretamente
|--------------------------------------------------------------------------
*/
if ($target instanceof Closure) {
    call_user_func($target);
    exit;
}

/*
|--------------------------------------------------------------------------
| 2. Extrair controller, método e middlewares
|--------------------------------------------------------------------------
*/
$controllerName = $target['controller'];
$methodName     = $target['method'];
$middlewares    = $target['middlewares'] ?? [];


/*
|--------------------------------------------------------------------------
| 3. Executar Middlewares
|--------------------------------------------------------------------------
*/
foreach ($middlewares as $mw) {

    // Autenticação
    if ($mw === 'auth' && !Auth::check()) {
        header("Location: /login");
        exit;
    }

    // Permissões
    if (str_starts_with($mw, 'perm:')) {
        $perm = substr($mw, 5);
        PermissaoMiddleware::handle($perm);
    }
}


/*
|--------------------------------------------------------------------------
| 4. Instanciar Controller
|--------------------------------------------------------------------------
*/
$controllerClass = "App\\Controllers\\$controllerName";

if (!class_exists($controllerClass)) {
    throw new Exception("Controller não encontrado: $controllerClass");
}

$controller = new $controllerClass();

if (!method_exists($controller, $methodName)) {
    throw new Exception("Método não encontrado: $methodName em $controllerClass");
}


/*
|--------------------------------------------------------------------------
| 5. Executar Controller + Params
|--------------------------------------------------------------------------
*/
call_user_func_array([$controller, $methodName], $match['params']);