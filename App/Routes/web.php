<?php
use App\Core\Route;
// Página 403 - Acesso negado
Route::get('/403', 'ErrorController@forbidden');
// Página 404 - Não encontrada
Route::get('/404', 'ErrorController@notFound');
/*
|--------------------------------------------------------------------------
| Rotas Públicas
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
| Autenticação de 2 Fatores (2FA)
|--------------------------------------------------------------------------
*/

Route::get('/2fa/ativar', 'TwoFactorController@ativar', ['auth']);
Route::post('/2fa/confirmar', 'TwoFactorController@confirmar', ['auth']);
Route::get('/2fa/validar', 'TwoFactorController@formValidar', ['auth']);
Route::post('/2fa/validar', 'TwoFactorController@validarCodigo', ['auth']);
Route::post('/2fa/desativar', 'TwoFactorController@desativar', ['auth', '2fa']);

/*
|--------------------------------------------------------------------------
| Painel
|--------------------------------------------------------------------------
*/

Route::get('/painel', 'PainelController@index', ['auth', '2fa']);
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

