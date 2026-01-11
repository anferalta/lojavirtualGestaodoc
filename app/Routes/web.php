<?php

use App\Core\Route;

/*
  |--------------------------------------------------------------------------
  | Páginas de Erro
  |--------------------------------------------------------------------------
 */
Route::get('/403', 'ErrorController@forbidden');
Route::get('/404', 'ErrorController@notFound');

/*
  |--------------------------------------------------------------------------
  | Rotas Públicas (sem autenticação)
  |--------------------------------------------------------------------------
 */
Route::get('/', 'AuthController@loginForm');
Route::get('/login', 'AuthController@loginForm');
Route::post('/login', 'AuthController@login', ['csrf']);
Route::get('/logout', 'AuthController@logout');

/* Recuperação de senha */
Route::get('/recuperar', 'AuthController@recuperar');
Route::post('/recuperar', 'AuthController@enviarRecuperacao');
Route::get('/redefinir', 'AuthController@formRedefinir');
Route::post('/redefinir', 'AuthController@redefinirSenha');

/*
  |--------------------------------------------------------------------------
  | Rotas autenticadas (sem 2FA obrigatório)
  |--------------------------------------------------------------------------
 */
Route::group(['middleware' => ['auth']], function () {

    Route::get('/2fa/ativar', 'TwoFactorController@ativar');
    Route::post('/2fa/confirmar', 'TwoFactorController@confirmar');

    Route::get('/2fa/validar', 'TwoFactorController@validarForm');
    Route::post('/2fa/validar', 'TwoFactorController@validar');
});

/*
  |--------------------------------------------------------------------------
  | Rotas autenticadas + 2FA obrigatório
  |--------------------------------------------------------------------------
 */
Route::group(['middleware' => ['auth', 'TwoFactorMiddleware']], function () {

    /* Dashboard */
    Route::get('/dashboard', 'DashboardController@index');
    Route::get('/painel', 'DashboardController@index'); // Alias opcional

    Route::get('/painel/seguranca', 'TwoFactorController@paginaSeguranca');
    Route::get('/painel/seguranca/dashboard', 'SegurancaController@index');

    /*
      |--------------------------------------------------------------------------
      | Documentos
      |--------------------------------------------------------------------------
      | Protegido por:
      | - auth
      | - 2fa
      | - acl:documentos.*
      |--------------------------------------------------------------------------
     */
    Route::group(['prefix' => '/documentos'], function () {

        Route::get('/', 'DocumentosController@index', ['acl:documentos.ver']);

        Route::get('/criar', 'DocumentosController@criar', ['acl:documentos.criar']);
        Route::post('/store', 'DocumentosController@store', ['acl:documentos.criar', 'csrf']);

        Route::get('/editar/{id}', 'DocumentosController@editar', ['acl:documentos.editar']);
        Route::post('/update/{id}', 'DocumentosController@update', ['acl:documentos.editar', 'csrf']);

        Route::get('/eliminar/{id}', 'DocumentosController@delete', ['acl:documentos.eliminar']);

        Route::get('/download/{id}', 'DocumentosController@download', ['acl:documentos.ver']);
        Route::get('/preview/{id}', 'DocumentosController@preview', ['acl:documentos.ver']);

        Route::get('/{id}', 'DocumentosController@show', ['acl:documentos.ver']);
    });

    /*
      |--------------------------------------------------------------------------
      | Utilizadores
      |--------------------------------------------------------------------------
     */
    Route::group(['prefix' => '/utilizadores'], function () {

        Route::get('/', 'UtilizadoresController@index', ['acl:utilizadores.ver']);

        Route::get('/criar', 'UtilizadoresController@criar', ['acl:utilizadores.criar']);
        Route::post('/criar', 'UtilizadoresController@store', ['acl:utilizadores.criar', 'csrf']);

        Route::get('/editar/{id}', 'UtilizadoresController@editar', ['acl:utilizadores.editar']);
        Route::post('/editar/{id}', 'UtilizadoresController@update', ['acl:utilizadores.editar', 'csrf']);

        Route::get('/eliminar/{id}', 'UtilizadoresController@delete', ['acl:utilizadores.eliminar']);

        Route::get('/{id}', 'UtilizadoresController@show', ['acl:utilizadores.ver']);
    });

    // 2FA
    Route::get('/2fa/setup', 'TwoFactorSetupController@setupForm');
    Route::post('/2fa/ativar', 'TwoFactorSetupController@ativar');
    Route::post('/2fa/desativar', 'TwoFactorSetupController@desativar');

// Recuperação de conta
    Route::get('/recuperar', 'RecuperacaoController@form');
    Route::post('/recuperar', 'RecuperacaoController@enviar');
    Route::get('/reset/{token}', 'RecuperacaoController@resetForm');
    Route::post('/reset/{token}', 'RecuperacaoController@reset');

    /*
      |--------------------------------------------------------------------------
      | Perfis e Permissões
      |--------------------------------------------------------------------------
     */
    Route::group(['prefix' => '/perfis'], function () {

        Route::get('/', 'PerfisController@index', ['acl:perfis.ver']);

        Route::get('/criar', 'PerfisController@criar', ['acl:perfis.criar']);
        Route::post('/store', 'PerfisController@store', ['acl:perfis.criar', 'csrf']);

        Route::get('/editar/{id}', 'PerfisController@editar', ['acl:perfis.editar']);
        Route::post('/update/{id}', 'PerfisController@update', ['acl:perfis.editar', 'csrf']);

        Route::get('/eliminar/{id}', 'PerfisController@delete', ['acl:perfis.eliminar']);
    });

    Route::group(['prefix' => '/permissoes'], function () {
        Route::get('/', 'PermissoesController@index', ['acl:permissoes.ver']);
        Route::post('/', 'PermissoesController@update', ['acl:permissoes.editar', 'csrf']);
    });

    // Perfil
    Route::get('/perfil', 'PerfilController@index', ['middleware' => ['auth', 'twofactor']]);

// Recuperação
    Route::get('/recuperar', 'RecuperacaoController@form');
    Route::post('/recuperar', 'RecuperacaoController@enviar');
    Route::get('/reset/{token}', 'RecuperacaoController@resetForm');
    Route::post('/reset/{token}', 'RecuperacaoController@reset');

// 2FA
    Route::get('/2fa/setup', 'TwoFactorSetupController@setupForm', ['middleware' => ['auth']]);
    Route::post('/2fa/ativar', 'TwoFactorSetupController@ativar', ['middleware' => ['auth']]);
    Route::post('/2fa/desativar', 'TwoFactorSetupController@desativar', ['middleware' => ['auth']]);

    /*
      |--------------------------------------------------------------------------
      | Admin (prefixo /admin)
      |--------------------------------------------------------------------------
     */
    Route::group(['prefix' => '/admin'], function () {

        Route::group(['prefix' => '/perfis'], function () {
            Route::get('/', 'PerfisAdminController@index', ['acl:perfis.ver']);
            Route::get('/criar', 'PerfisAdminController@create', ['acl:perfis.criar']);
            Route::post('/criar', 'PerfisAdminController@store', ['acl:perfis.criar', 'csrf']);
            Route::get('/editar/{id}', 'PerfisAdminController@edit', ['acl:perfis.editar']);
            Route::post('/editar/{id}', 'PerfisAdminController@update', ['acl:perfis.editar', 'csrf']);
            Route::get('/eliminar/{id}', 'PerfisAdminController@delete', ['acl:perfis.eliminar']);
        });

        Route::group(['prefix' => '/utilizadores'], function () {
            Route::get('/', 'UtilizadoresAdminController@index', ['acl:utilizadores.ver']);
            Route::get('/criar', 'UtilizadoresAdminController@create', ['acl:utilizadores.criar']);
            Route::post('/criar', 'UtilizadoresAdminController@store', ['acl:utilizadores.criar', 'csrf']);
            Route::get('/editar/{id}', 'UtilizadoresAdminController@edit', ['acl:utilizadores.editar']);
            Route::post('/editar/{id}', 'UtilizadoresAdminController@update', ['acl:utilizadores.editar', 'csrf']);
            Route::get('/eliminar/{id}', 'UtilizadoresAdminController@delete', ['acl:utilizadores.eliminar']);
        });
    });
});
