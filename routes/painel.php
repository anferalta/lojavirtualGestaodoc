<?php

$router->group(['middleware' => ['auth']], function () use ($router) {

    $router->get('/2fa/ativar', 'TwoFactorController@ativar');
    $router->post('/2fa/confirmar', 'TwoFactorController@confirmar');

    $router->get('/2fa/validar', 'TwoFactorController@validarForm');
    $router->post('/2fa/validar', 'TwoFactorController@validar');
});

$router->group(['middleware' => ['auth', 'TwoFactorMiddleware']], function () use ($router) {

    $router->get('/dashboard', 'DashboardController@index');
    $router->get('/painel', 'DashboardController@index');

    $router->get('/painel/seguranca', 'TwoFactorController@paginaSeguranca');
    $router->get('/painel/seguranca/dashboard', 'SegurancaController@index');

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

    $router->group(['prefix' => '/utilizadores'], function () use ($router) {
        $router->get('/', 'UtilizadoresController@index', ['acl:utilizadores.ver']);
        $router->get('/criar', 'UtilizadoresController@criar', ['acl:utilizadores.criar']);
        $router->post('/criar', 'UtilizadoresController@store', ['acl:utilizadores.criar', 'csrf']);
        $router->get('/editar/{id}', 'UtilizadoresController@editar', ['acl:utilizadores.editar']);
        $router->post('/editar/{id}', 'UtilizadoresController@update', ['acl:utilizadores.editar', 'csrf']);
        $router->get('/eliminar/{id}', 'UtilizadoresController@delete', ['acl:utilizadores.eliminar']);
        $router->get('/{id}', 'UtilizadoresController@show', ['acl:utilizadores.ver']);
    });

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
});