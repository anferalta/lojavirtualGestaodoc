use app\Core\Acl;
use app\Core\Auth;
use app\Core\Sessao;
use app\Core\Helpers;

Middleware::register('perm', function ($permissionKey) {

    if (!Auth::check()) {
        Sessao::flash('É necessário autenticação.', 'danger');
        Helpers::redirecionar('/login');
        exit;
    }

    if (!Acl::can($permissionKey)) {
        http_response_code(403);
        Sessao::flash('Não tem permissão para aceder a esta área.', 'danger');
        Helpers::redirecionar('/painel');
        exit;
    }
});