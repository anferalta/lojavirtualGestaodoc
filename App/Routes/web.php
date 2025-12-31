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
Route::get('/', 'LoginController@index');
Route::get('/login', 'LoginController@index');
Route::post('/login', 'LoginController@autenticar');
Route::get('/logout', 'LoginController@logout');

// Recuperação de senha
Route::get('/recuperar', 'LoginController@recuperar');
Route::post('/recuperar', 'LoginController@enviarRecuperacao');
Route::get('/redefinir', 'LoginController@formRedefinir');
Route::post('/redefinir', 'LoginController@redefinirSenha');


/*
|--------------------------------------------------------------------------
| Rotas protegidas por autenticação
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', '2fa']);


/*
|--------------------------------------------------------------------------
| Painel
|--------------------------------------------------------------------------
*/
Route::get('/painel', 'PainelController@index');
Route::get('/dashboard', 'PainelController@index');

Route::get('/painel/seguranca', 'TwoFactorController@paginaSeguranca');
Route::get('/painel/seguranca/dashboard', 'SegurancaController@index');


/*
|--------------------------------------------------------------------------
| 2FA
|--------------------------------------------------------------------------
*/
Route::get('/2fa/ativar', 'TwoFactorController@ativar');
Route::post('/2fa/confirmar', 'TwoFactorController@confirmar');
Route::get('/2fa/validar', 'TwoFactorController@formValidar');
Route::post('/2fa/validar', 'TwoFactorController@validarCodigo');
Route::post('/2fa/desativar', 'TwoFactorController@desativar', ['2fa']);


/*
|--------------------------------------------------------------------------
| Documentos
|--------------------------------------------------------------------------
*/
Route::get('/documentos', 'DocumentosController@index', ['perm:documentos.ver']);
Route::get('/documentos/upload', 'DocumentosController@upload', ['perm:documentos.criar']);
Route::post('/documentos/store', 'DocumentosController@store', ['perm:documentos.criar', 'csrf']);

Route::get('/documentos/editar/{id}', 'DocumentosController@editar', ['perm:documentos.editar']);
Route::post('/documentos/update/{id}', 'DocumentosController@update', ['perm:documentos.editar', 'csrf']);

Route::get('/documentos/eliminar/{id}', 'DocumentosController@delete', ['perm:documentos.eliminar']);
Route::get('/documentos/download/{id}', 'DocumentosController@download', ['perm:documentos.ver']);

$router->get('/documentos/download/{id}', 'DocumentosController@download', ['auth', 'perm:documentos.ver']);

$router->get('/documentos/preview/{id}', 'DocumentosController@preview', ['auth', 'perm:documentos.ver']);


/*
|--------------------------------------------------------------------------
| Utilizadores
|--------------------------------------------------------------------------
*/
Route::get('/utilizadores', 'UtilizadoresController@index', ['perm:utilizadores.ver']);
Route::get('/utilizadores/criar', 'UtilizadoresController@criar', ['perm:utilizadores.criar']);
Route::post('/utilizadores/criar', 'UtilizadoresController@store', ['perm:utilizadores.criar', 'csrf']);

Route::get('/utilizadores/editar/{id}', 'UtilizadoresController@editar', ['perm:utilizadores.editar']);
Route::post('/utilizadores/editar/{id}', 'UtilizadoresController@update', ['perm:utilizadores.editar', 'csrf']);

Route::get('/utilizadores/eliminar/{id}', 'UtilizadoresController@delete', ['perm:utilizadores.eliminar']);
Route::get('/utilizadores/{id}', 'UtilizadoresController@show', ['perm:utilizadores.ver']);


/*
|--------------------------------------------------------------------------
| Perfis
|--------------------------------------------------------------------------
*/
Route::get('/perfis', 'PerfisController@index', ['perm:perfis.ver']);
Route::get('/perfis/criar', 'PerfisController@criar', ['perm:perfis.criar']);
Route::post('/perfis/store', 'PerfisController@store', ['perm:perfis.criar', 'csrf']);

Route::get('/perfis/editar/{id}', 'PerfisController@editar', ['perm:perfis.editar']);
Route::post('/perfis/update/{id}', 'PerfisController@update', ['perm:perfis.editar', 'csrf']);

Route::get('/perfis/eliminar/{id}', 'PerfisController@delete', ['perm:perfis.eliminar']);