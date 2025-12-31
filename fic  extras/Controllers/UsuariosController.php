<?php
namespace app\Controllers;

use app\Core\BaseController;
use app\Core\Helpers;
use app\Core\Validator;
use app\Core\Auth;
use app\Model\UsuarioModel;

class UsuariosController extends BaseController
{
    private UsuarioModel $user;

    public function __construct()
    {
        parent::__construct();
        $this->user = new UsuarioModel();
    }

    /*
    |--------------------------------------------------------------------------
    | LISTAGEM
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $total = $this->user->count();
        $pages = ceil($total / $limit);

        echo $this->twig->render('utilizadores/list.twig', [
            'titulo'       => 'Gestão de Utilizadores',
            'utilizadores' => $this->user->paginate($limit, $offset),
            'page'         => $page,
            'pages'        => $pages
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CRIAR
    |--------------------------------------------------------------------------
    */

    public function criar(): void
    {
        echo $this->twig->render('utilizadores/form.twig', [
            'titulo' => 'Criar Utilizador',
            'acao'   => 'criar'
        ]);
    }

    public function store(): void
    {
        $validator = new Validator();

        $validator->required('nome', $_POST['nome'], 'O nome é obrigatório.');
        $validator->required('email', $_POST['email'], 'O email é obrigatório.');
        $validator->email('email', $_POST['email'], 'O email é inválido.');
        $validator->required('senha', $_POST['senha'], 'A senha é obrigatória.');
        $validator->min('senha', $_POST['senha'], 6, 'A senha deve ter pelo menos 6 caracteres.');

        // Verificar duplicação de email
        if ($this->user->findByEmail($_POST['email'])) {
            $validator->addError('email', 'Este email já está registado.');
        }

        if ($validator->hasErrors()) {
            $_SESSION['form_errors'] = $validator->getErrors();
            $_SESSION['form_old'] = $_POST;

            Helpers::redirecionar('/utilizadores/criar');
        }

        // Hash da senha
        $_POST['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        $this->user->create($_POST);

        Auth::logAuth(Auth::id(), Auth::email(), 'utilizador_criado');

        $this->sessao->setFlash("Utilizador criado com sucesso!", "success");
        Helpers::redirecionar('/utilizadores');
    }

    /*
    |--------------------------------------------------------------------------
    | EDITAR
    |--------------------------------------------------------------------------
    */

    public function editar(int $id): void
    {
        echo $this->twig->render('utilizadores/form.twig', [
            'titulo' => 'Editar Utilizador',
            'acao'   => 'editar',
            'user'   => $this->user->find($id)
        ]);
    }

    public function update(int $id): void
    {
        $validator = new Validator();

        $validator->required('nome', $_POST['nome'], 'O nome é obrigatório.');
        $validator->required('email', $_POST['email'], 'O email é obrigatório.');
        $validator->email('email', $_POST['email'], 'O email é inválido.');

        // Verificar duplicação de email (exceto o próprio)
        $existing = $this->user->findByEmail($_POST['email']);
        if ($existing && $existing->id != $id) {
            $validator->addError('email', 'Este email já está registado.');
        }

        if ($validator->hasErrors()) {
            $_SESSION['form_errors'] = $validator->getErrors();
            $_SESSION['form_old'] = $_POST;

            Helpers::redirecionar("/utilizadores/editar/$id");
        }

        $this->user->update($id, $_POST);

        Auth::logAuth(Auth::id(), Auth::email(), 'utilizador_editado');

        $this->sessao->setFlash("Utilizador atualizado!", "success");
        Helpers::redirecionar('/utilizadores');
    }

    /*
    |--------------------------------------------------------------------------
    | APAGAR
    |--------------------------------------------------------------------------
    */

    public function delete(int $id): void
    {
        $this->user->delete($id);

        Auth::logAuth(Auth::id(), Auth::email(), 'utilizador_removido');

        $this->sessao->setFlash("Utilizador removido!", "danger");
        Helpers::redirecionar('/utilizadores');
    }
}