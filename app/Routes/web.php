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
| Rotas Públicas (sem auth)
|--------------------------------------------------------------------------
*/
Route::get('/', 'AuthController@loginForm');
Route::get('/login', 'AuthController@loginForm');
Route::post('/login', 'AuthController@login');
Route::get('/logout', 'AuthController@logout');

/* Recuperação de senha */
Route::get('/recuperar', 'AuthController@recuperar');
Route::post('/recuperar', 'AuthController@enviarRecuperacao');
Route::get('/redefinir', 'AuthController@formRedefinir');
Route::post('/redefinir', 'AuthController@redefinirSenha');


/*
|--------------------------------------------------------------------------
| Rotas autenticadas mas SEM 2FA obrigatório
|--------------------------------------------------------------------------
*/
Route::get('/2fa/ativar', 'TwoFactorController@ativar', ['auth']);
Route::post('/2fa/confirmar', 'TwoFactorController@confirmar', ['auth']);
Route::get('/2fa/validar', 'TwoFactorController@formValidar', ['auth']);
Route::post('/2fa/validar', 'TwoFactorController@validarCodigo', ['auth']);


/*
|--------------------------------------------------------------------------
| Rotas autenticadas + 2FA obrigatório
|--------------------------------------------------------------------------
*/
Route::get('/painel', 'DashboardController@index', ['auth', '2fa']);
Route::get('/dashboard', 'DashboardController@index', ['auth', '2fa']);

Route::get('/painel/seguranca', 'TwoFactorController@paginaSeguranca', ['auth', '2fa']);
Route::get('/painel/seguranca/dashboard', 'SegurancaController@index', ['auth', '2fa']);


/*
|--------------------------------------------------------------------------
| Documentos
|--------------------------------------------------------------------------
*/
Route::get('/documentos', 'DocumentosController@index', ['auth', '2fa', 'perm:documentos.ver']);
Route::get('/documentos/upload', 'DocumentosController@upload', ['auth', '2fa', 'perm:documentos.criar']);
Route::post('/documentos/store', 'DocumentosController@store', ['auth', '2fa', 'perm:documentos.criar', 'csrf']);

Route::get('/documentos/editar/{id}', 'DocumentosController@editar', ['auth', '2fa', 'perm:documentos.editar']);
Route::post('/documentos/update/{id}', 'DocumentosController@update', ['auth', '2fa', 'perm:documentos.editar', 'csrf']);

Route::get('/documentos/eliminar/{id}', 'DocumentosController@delete', ['auth', '2fa', 'perm:documentos.eliminar']);
Route::get('/documentos/download/{id}', 'DocumentosController@download', ['auth', '2fa', 'perm:documentos.ver']);
Route::get('/documentos/preview/{id}', 'DocumentosController@preview', ['auth', '2fa', 'perm:documentos.ver']);


/*
|--------------------------------------------------------------------------
| Utilizadores
|--------------------------------------------------------------------------
*/
Route::get('/utilizadores', 'UtilizadoresController@index', ['auth', '2fa', 'perm:utilizadores.ver']);
Route::get('/utilizadores/criar', 'UtilizadoresController@criar', ['auth', '2fa', 'perm:utilizadores.criar']);
Route::post('/utilizadores/criar', 'UtilizadoresController@store', ['auth', '2fa', 'perm:utilizadores.criar', 'csrf']);

Route::get('/utilizadores/editar/{id}', 'UtilizadoresController@editar', ['auth', '2fa', 'perm:utilizadores.editar']);
Route::post('/utilizadores/editar/{id}', 'UtilizadoresController@update', ['auth', '2fa', 'perm:utilizadores.editar', 'csrf']);

Route::get('/utilizadores/eliminar/{id}', 'UtilizadoresController@delete', ['auth', '2fa', 'perm:utilizadores.eliminar']);
Route::get('/utilizadores/{id}', 'UtilizadoresController@show', ['auth', '2fa', 'perm:utilizadores.ver']);


/*
|--------------------------------------------------------------------------
| Perfis
|--------------------------------------------------------------------------
*/
Route::get('/perfis', 'PerfisController@index', ['auth', '2fa', 'perm:perfis.ver']);
Route::get('/perfis/criar', 'PerfisController@criar', ['auth', '2fa', 'perm:perfis.criar']);
Route::post('/perfis/store', 'PerfisController@store', ['auth', '2fa', 'perm:perfis.criar', 'csrf']);

Route::get('/perfis/editar/{id}', 'PerfisController@editar', ['auth', '2fa', 'perm:perfis.editar']);
Route::post('/perfis/update/{id}', 'PerfisController@update', ['auth', '2fa', 'perm:perfis.editar', 'csrf']);

Route::get('/perfis/eliminar/{id}', 'PerfisController@delete', ['auth', '2fa', 'perm:perfis.eliminar']);