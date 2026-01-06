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
Route::post('/login', 'AuthController@login', ['csrf']);
Route::get('/login', 'AuthController@loginForm');
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
|
| Todas as rotas protegidas por:
| - auth  → utilizador autenticado
| - 2fa   → segundo fator ativo
| - acl   → permissões específicas
|
*/

# LISTAR DOCUMENTOS
Route::get('/documentos', 'DocumentosController@index', [
    'auth', '2fa', 'acl:documentos.ver'
]);

# FORMULÁRIO DE CRIAÇÃO
Route::get('/documentos/criar', 'DocumentosController@criar', [
    'auth', '2fa', 'acl:documentos.criar'
]);

# GUARDAR NOVO DOCUMENTO
Route::post('/documentos/store', 'DocumentosController@store', [
    'auth', '2fa', 'acl:documentos.criar', 'csrf'
]);

# EDITAR DOCUMENTO
Route::get('/documentos/editar/{id}', 'DocumentosController@editar', [
    'auth', '2fa', 'acl:documentos.editar'
]);

Route::post('/documentos/update/{id}', 'DocumentosController@update', [
    'auth', '2fa', 'acl:documentos.editar', 'csrf'
]);

# ELIMINAR DOCUMENTO
Route::get('/documentos/eliminar/{id}', 'DocumentosController@delete', [
    'auth', '2fa', 'acl:documentos.eliminar'
]);

# DOWNLOAD
Route::get('/documentos/download/{id}', 'DocumentosController@download', [
    'auth', '2fa', 'acl:documentos.ver'
]);

# PREVIEW (imagem/pdf)
Route::get('/documentos/preview/{id}', 'DocumentosController@preview', [
    'auth', '2fa', 'acl:documentos.ver'
]);

# DETALHES DO DOCUMENTO
Route::get('/documentos/{id}', 'DocumentosController@show', [
    'auth', '2fa', 'acl:documentos.ver'
]);

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

Route::get('/permissoes', 'PermissoesController@index', ['auth', '2fa', 'perm:permissoes.ver']);
Route::post('/permissoes', 'PermissoesController@update', ['auth', '2fa', 'perm:permissoes.editar', 'csrf']);

Route::get('/admin/perfis', 'PerfisAdminController@index', ['auth', '2fa', 'perm:perfis.ver']);
Route::get('/admin/perfis/criar', 'PerfisAdminController@create', ['auth', '2fa', 'perm:perfis.criar']);
Route::post('/admin/perfis/criar', 'PerfisAdminController@store', ['auth', '2fa', 'perm:perfis.criar', 'csrf']);
Route::get('/admin/perfis/editar/{id}', 'PerfisAdminController@edit', ['auth', '2fa', 'perm:perfis.editar']);
Route::post('/admin/perfis/editar/{id}', 'PerfisAdminController@update', ['auth', '2fa', 'perm:perfis.editar', 'csrf']);
Route::get('/admin/perfis/eliminar/{id}', 'PerfisAdminController@delete', ['auth', '2fa', 'perm:perfis.eliminar']);

Route::get('/admin/utilizadores', 'UtilizadoresAdminController@index', ['auth', '2fa', 'perm:utilizadores.ver']);
Route::get('/admin/utilizadores/criar', 'UtilizadoresAdminController@create', ['auth', '2fa', 'perm:utilizadores.criar']);
Route::post('/admin/utilizadores/criar', 'UtilizadoresAdminController@store', ['auth', '2fa', 'perm:utilizadores.criar', 'csrf']);
Route::get('/admin/utilizadores/editar/{id}', 'UtilizadoresAdminController@edit', ['auth', '2fa', 'perm:utilizadores.editar']);
Route::post('/admin/utilizadores/editar/{id}', 'UtilizadoresAdminController@update', ['auth', '2fa', 'perm:utilizadores.editar', 'csrf']);
Route::get('/admin/utilizadores/eliminar/{id}', 'UtilizadoresAdminController@delete', ['auth', '2fa', 'perm:utilizadores.eliminar']);

