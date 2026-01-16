<?php

$router->group([
    'prefix' => '/admin',
    'middleware' => ['auth', 'TwoFactorMiddleware']
], function () use ($router) {

    /*
    |--------------------------------------------------------------------------
    | Gestão de Perfis (Admin - ORM Moderno)
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/perfis'], function () use ($router) {

        // CRUD de Perfis
        $router->get('/', 'PerfisController@index', ['acl:perfis.ver']);
        $router->get('/criar', 'PerfisController@create', ['acl:perfis.criar']);
        $router->post('/criar', 'PerfisController@store', ['acl:perfis.criar', 'csrf']);
        $router->get('/editar/{id}', 'PerfisController@edit', ['acl:perfis.editar']);
        $router->post('/editar/{id}', 'PerfisController@update', ['acl:perfis.editar', 'csrf']);
        $router->post('/eliminar/{id}', 'PerfisController@delete', ['acl:perfis.eliminar', 'csrf']);

        // Atribuição de permissões ao perfil
        $router->get('/permissoes/{id}', 'PerfisController@permissions', ['acl:perfis.editar']);
        $router->post('/permissoes/{id}', 'PerfisController@savePermissions', ['acl:perfis.editar', 'csrf']);
    });


    /*
    |--------------------------------------------------------------------------
    | Gestão de Utilizadores (Admin)
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/utilizadores'], function () use ($router) {

        $router->get('/', 'Admin\\UtilizadoresAdminController@index', ['acl:utilizadores.ver']);
        $router->get('/criar', 'Admin\\UtilizadoresAdminController@criar', ['acl:utilizadores.criar']);
        $router->post('/criar', 'Admin\\UtilizadoresAdminController@criarSubmit', ['acl:utilizadores.criar', 'csrf']);
        $router->get('/editar/{id}', 'Admin\\UtilizadoresAdminController@editar', ['acl:utilizadores.editar']);
        $router->post('/editar/{id}', 'Admin\\UtilizadoresAdminController@editarSubmit', ['acl:utilizadores.editar', 'csrf']);
        $router->get('/apagar/{id}', 'Admin\\UtilizadoresAdminController@apagar', ['acl:utilizadores.apagar']);
    });


    /*
    |--------------------------------------------------------------------------
    | Gestão de Permissões (Admin)
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/permissoes'], function () use ($router) {

        $router->get('/', 'PermissoesController@index', ['acl:permissoes.ver']);
        $router->get('/criar', 'PermissoesController@create', ['acl:permissoes.criar']);
        $router->post('/criar', 'PermissoesController@store', ['acl:permissoes.criar', 'csrf']);
        $router->get('/editar/{id}', 'PermissoesController@edit', ['acl:permissoes.editar']);
        $router->post('/editar/{id}', 'PermissoesController@update', ['acl:permissoes.editar', 'csrf']);
        $router->post('/eliminar/{id}', 'PermissoesController@delete', ['acl:permissoes.eliminar', 'csrf']);
    });

});