<?php

/** @var \App\Core\Router $router */

/*
|--------------------------------------------------------------------------
| Páginas de Erro
|--------------------------------------------------------------------------
*/
$router->get('/403', 'ErrorController@forbidden');
$router->get('/404', 'ErrorController@notFound');

/*
|--------------------------------------------------------------------------
| Rotas Públicas (sem autenticação)
|--------------------------------------------------------------------------
*/
$router->get('/', 'AuthController@loginForm');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login', ['csrf']);
$router->get('/logout', 'AuthController@logout');

/* Recuperação de senha */
$router->get('/recuperar', 'AuthController@recuperar');
$router->post('/recuperar', 'AuthController@enviarRecuperacao');
$router->get('/redefinir', 'AuthController@formRedefinir');
$router->post('/redefinir', 'AuthController@redefinirSenha');

/*
|--------------------------------------------------------------------------
| Rotas autenticadas (sem 2FA obrigatório)
|--------------------------------------------------------------------------
*/
$router->group(['middleware' => ['auth']], function () use ($router) {

    $router->get('/2fa/ativar', 'TwoFactorController@ativar');
    $router->post('/2fa/confirmar', 'TwoFactorController@confirmar');

    $router->get('/2fa/validar', 'TwoFactorController@validarForm');
    $router->post('/2fa/validar', 'TwoFactorController@validar');
});

/*
|--------------------------------------------------------------------------
| Rotas autenticadas + 2FA obrigatório
|--------------------------------------------------------------------------
*/
$router->group(['middleware' => ['auth', 'TwoFactorMiddleware']], function () use ($router) {

    /* Dashboard */
    $router->get('/dashboard', 'DashboardController@index');
    $router->get('/painel', 'DashboardController@index');

    $router->get('/painel/seguranca', 'TwoFactorController@paginaSeguranca');
    $router->get('/painel/seguranca/dashboard', 'SegurancaController@index');

    /*
    |--------------------------------------------------------------------------
    | Documentos
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/documentos'], function () use ($router) {

        $router->get('/', 'DocumentosController@index', ['acl:documentos.ver']);

        $router->get('/criar', 'DocumentosController@criar', ['acl:documentos.criar']);
        $router->post('/store', 'DocumentosController@store', ['acl:documentos.criar', 'csrf']);

        $router->get('/editar/{id}', 'DocumentosController@editar', ['acl:documentos.editar']);
        $router->post('/update/{id}', 'DocumentosController@update', ['acl:documentos.editar', 'csrf']);

        $router->get('/eliminar/{id}', 'DocumentosController@delete', ['acl:documentos.eliminar']);

        $router->get('/download/{id}', 'DocumentosController@download', ['acl:documentos.ver']);
        $router->get('/preview/{id}', 'DocumentosController@preview', ['acl:documentos.ver']);

        $router->get('/{id}', 'DocumentosController@show', ['acl:documentos.ver']);
    });

    /*
    |--------------------------------------------------------------------------
    | Utilizadores (módulo normal)
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/utilizadores'], function () use ($router) {

        $router->get('/', 'UtilizadoresController@index', ['acl:utilizadores.ver']);

        $router->get('/criar', 'UtilizadoresController@criar', ['acl:utilizadores.criar']);
        $router->post('/criar', 'UtilizadoresController@store', ['acl:utilizadores.criar', 'csrf']);

        $router->get('/editar/{id}', 'UtilizadoresController@editar', ['acl:utilizadores.editar']);
        $router->post('/editar/{id}', 'UtilizadoresController@update', ['acl:utilizadores.editar', 'csrf']);

        $router->get('/eliminar/{id}', 'UtilizadoresController@delete', ['acl:utilizadores.eliminar']);

        $router->get('/{id}', 'UtilizadoresController@show', ['acl:utilizadores.ver']);
    });

    /*
    |--------------------------------------------------------------------------
    | Perfis e Permissões (módulo normal)
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/perfis'], function () use ($router) {

        $router->get('/', 'PerfisController@index', ['acl:perfis.ver']);

        $router->get('/criar', 'PerfisController@criar', ['acl:perfis.criar']);
        $router->post('/store', 'PerfisController@store', ['acl:perfis.criar', 'csrf']);

        $router->get('/editar/{id}', 'PerfisController@editar', ['acl:perfis.editar']);
        $router->post('/update/{id}', 'PerfisController@update', ['acl:perfis.editar', 'csrf']);

        $router->get('/eliminar/{id}', 'PerfisController@delete', ['acl:perfis.eliminar']);
    });

    $router->group(['prefix' => '/permissoes'], function () use ($router) {
        $router->get('/', 'PermissoesController@index', ['acl:permissoes.ver']);
        $router->post('/', 'PermissoesController@update', ['acl:permissoes.editar', 'csrf']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin (prefixo /admin)
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/admin'], function () use ($router) {

        /* Perfis admin */
        $router->group(['prefix' => '/perfis'], function () use ($router) {
            $router->get('/', 'Admin\\PerfisAdminController@index', ['acl:perfis.ver']);
            $router->get('/criar', 'Admin\\PerfisAdminController@create', ['acl:perfis.criar']);
            $router->post('/criar', 'Admin\\PerfisAdminController@store', ['acl:perfis.criar', 'csrf']);
            $router->get('/editar/{id}', 'Admin\\PerfisAdminController@edit', ['acl:perfis.editar']);
            $router->post('/editar/{id}', 'Admin\\PerfisAdminController@update', ['acl:perfis.editar', 'csrf']);
            $router->get('/eliminar/{id}', 'Admin\\PerfisAdminController@delete', ['acl:perfis.eliminar']);
        });

        /* Utilizadores admin */
        $router->group(['prefix' => '/utilizadores'], function () use ($router) {
            $router->get('/', 'Admin\\UtilizadoresAdminController@index', ['acl:utilizadores.ver']);
            $router->get('/criar', 'Admin\\UtilizadoresAdminController@criar', ['acl:utilizadores.criar']);
            $router->post('/criar', 'Admin\\UtilizadoresAdminController@criarSubmit', ['acl:utilizadores.criar', 'csrf']);
            $router->get('/editar/{id}', 'Admin\\UtilizadoresAdminController@editar', ['acl:utilizadores.editar']);
            $router->post('/editar/{id}', 'Admin\\UtilizadoresAdminController@editarSubmit', ['acl:utilizadores.editar', 'csrf']);
            $router->get('/apagar/{id}', 'Admin\\UtilizadoresAdminController@apagar', ['acl:utilizadores.apagar']);
        });

    });

});