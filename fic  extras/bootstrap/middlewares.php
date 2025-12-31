<?php

use app\Core\Sessao;
use app\Core\Helpers;
use app\Core\Permission;
use app\Core\Conexao;

/**
 * ---------------------------------------------------------
 * MIDDLEWARE: Autenticação
 * ---------------------------------------------------------
 * Bloqueia acesso a rotas privadas se o utilizador não estiver autenticado.
 */
$auth = function() {
    if (!Sessao::get('user_id')) {
        Sessao::flash('Precisa de iniciar sessão.', 'danger');
        Helpers::redirecionar('/login');
    }
};


/**
 * ---------------------------------------------------------
 * MIDDLEWARE: Guest
 * ---------------------------------------------------------
 * Impede utilizadores autenticados de aceder ao login/registo.
 */
$guest = function() {
    if (Sessao::get('user_id')) {
        Helpers::redirecionar('/dashboard');
    }
};


/**
 * ---------------------------------------------------------
 * MIDDLEWARE: CSRF
 * ---------------------------------------------------------
 * Protege rotas POST/PUT/DELETE contra ataques CSRF.
 */
$csrf = function() {
    $method = $_SERVER['REQUEST_METHOD'];

    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        $token = $_POST['_csrf'] ?? '';

        if (!Sessao::validarCsrf($token)) {
            Sessao::flash('Token CSRF inválido.', 'danger');
            Helpers::redirecionar($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }
};


/**
 * ---------------------------------------------------------
 * MIDDLEWARE: ACL (Permissões)
 * ---------------------------------------------------------
 * Exemplo: proteger rotas com permissões específicas.
 *
 * Uso no Route:
 *   Route::get('/admin', 'AdminController@index', ['auth', 'acl:admin.aceder']);
 */
$acl = function($permissao) {
    $userId = Sessao::get('user_id');

    if (!$userId) {
        Sessao::flash('Precisa de iniciar sessão.', 'danger');
        Helpers::redirecionar('/login');
    }

    $perm = new Permission(Conexao::getInstancia());

    if (!$perm->userHas($userId, $permissao)) {
        Sessao::flash('Não tem permissão para aceder a esta área.', 'danger');
        Helpers::redirecionar('/403');
    }
};


/**
 * ---------------------------------------------------------
 * REGISTO DOS MIDDLEWARES
 * ---------------------------------------------------------
 * Estes nomes são usados no Route::dispatch()
 */
return [
    'auth' => $auth,
    'guest' => $guest,
    'csrf' => $csrf,
    'acl' => $acl,
];