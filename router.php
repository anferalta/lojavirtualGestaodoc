<?php

use app\Core\Helpers;
use app\Core\Auth;
use app\Middlewares\PermissaoMiddleware;

// ======================================================
// 1. Carregar o Router (AltoRouter ou o teu router custom)
// ======================================================

$router = new AltoRouter();

// Base path (se necessário)
$router->setBasePath('/');

// ======================================================
// 2. Definir rotas
// ======================================================

// Página inicial
$router->map('GET', '/', 'HomeController@index', 'home');

// Login
$router->map('GET', '/login', 'AuthController@login', 'login');
$router->map('POST', '/login', 'AuthController@entrar', 'login_post');
$router->map('GET', '/logout', 'AuthController@sair', 'logout');

// Utilizadores
$router->map('GET', '/utilizadores', 'UtilizadoresController@index', ['auth', 'perm:utilizadores.ver']);
$router->map('GET', '/utilizadores/criar', 'UtilizadoresController@criar', ['auth', 'perm:utilizadores.criar']);
$router->map('POST', '/utilizadores/criar', 'UtilizadoresController@store', ['auth', 'perm:utilizadores.criar']);
$router->map('GET', '/utilizadores/editar/[i:id]', 'UtilizadoresController@editar', ['auth', 'perm:utilizadores.editar']);
$router->map('POST', '/utilizadores/editar/[i:id]', 'UtilizadoresController@update', ['auth', 'perm:utilizadores.editar']);
$router->map('POST', '/utilizadores/eliminar/[i:id]', 'UtilizadoresController@delete', ['auth', 'perm:utilizadores.eliminar']);

// Perfis
$router->map('GET', '/perfis', 'PerfisController@index', ['auth', 'perm:perfis.ver']);
$router->map('GET', '/perfis/criar', 'PerfisController@criar', ['auth', 'perm:perfis.criar']);
$router->map('POST', '/perfis/criar', 'PerfisController@store', ['auth', 'perm:perfis.criar']);
$router->map('GET', '/perfis/editar/[i:id]', 'PerfisController@editar', ['auth', 'perm:perfis.editar']);
$router->map('POST', '/perfis/editar/[i:id]', 'PerfisController@update', ['auth', 'perm:perfis.editar']);
$router->map('POST', '/perfis/eliminar/[i:id]', 'PerfisController@delete', ['auth', 'perm:perfis.eliminar']);

// Permissões
$router->map('GET', '/permissoes', 'PermissoesController@index', ['auth', 'perm:permissoes.ver']);
$router->map('GET', '/permissoes/criar', 'PermissoesController@criar', ['auth', 'perm:permissoes.criar']);
$router->map('POST', '/permissoes/criar', 'PermissoesController@store', ['auth', 'perm:permissoes.criar']);
$router->map('GET', '/permissoes/editar/[i:id]', 'PermissoesController@editar', ['auth', 'perm:permissoes.editar']);
$router->map('POST', '/permissoes/editar/[i:id]', 'PermissoesController@update', ['auth', 'perm:permissoes.editar']);
$router->map('POST', '/permissoes/eliminar/[i:id]', 'PermissoesController@delete', ['auth', 'perm:permissoes.eliminar']);

// Documentos
$router->map('GET', '/documentos', 'DocumentosController@index', ['auth', 'perm:documentos.ver']);
$router->map('GET', '/documentos/criar', 'DocumentosController@criar', ['auth', 'perm:documentos.criar']);
$router->map('POST', '/documentos/criar', 'DocumentosController@store', ['auth', 'perm:documentos.criar']);
$router->map('GET', '/documentos/download/[i:id]', 'DocumentosController@download', ['auth', 'perm:documentos.download']);
$router->map('GET', '/documentos/preview/[i:id]', 'DocumentosController@preview', ['auth', 'perm:documentos.ver']);
$router->map('POST', '/documentos/eliminar/[i:id]', 'DocumentosController@delete', ['auth', 'perm:documentos.eliminar']);

$router->map('GET', '/', 'DashboardController@index', ['auth']);

$router->map('GET', '/auditoria', 'AuditoriaController@index', ['auth', 'perm:auditoria.ver']);

// ======================================================
// 3. Dispatcher
// ======================================================

$match = $router->match();

if (!$match) {
    (new app\Controllers\ErrorController())->error404();
    exit;
}

// Middlewares
if (is_array($match['target'])) {
    $middlewares = $match['target'][1] ?? [];
} else {
    $middlewares = $match['params'] ?? [];
}

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

// Controller + método
list($controller, $method) = explode('@', $match['target']);

$controller = "app\\Controllers\\$controller";
$instance = new $controller();

call_user_func_array([$instance, $method], $match['params']);