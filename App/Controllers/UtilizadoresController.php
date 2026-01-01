<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use App\Core\Usuario;
use App\Core\Validator;
use App\Core\Sessao;
use App\Core\Helpers;

class UtilizadoresController extends BaseController
{
    private Usuario $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new Usuario(Conexao::getInstancia());
    }

    public function index(): void
    {
        $pagina = max((int) ($_GET['pagina'] ?? 1), 1);
        $limite = 15;
        $offset = ($pagina - 1) * $limite;

        echo $this->twig->render('utilizadores/index.twig', [
            'utilizadores' => $this->userModel->paginate($limite, $offset),
            'pagina'       => $pagina,
            'totalPaginas' => ceil($this->userModel->count() / $limite),
        ]);
    }

    public function show(int $id): void
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            Sessao::flash('Utilizador não encontrado.', 'danger');
            return Helpers::redirecionar('/utilizadores');
        }

        echo $this->twig->render('utilizadores/show.twig', [
            'user' => $user,
        ]);
    }

    public function criar(): void
    {
        echo $this->twig->render('utilizadores/form.twig', [
            'acao'        => 'criar',
            'user'        => null,
            'form_old'    => [],
            'form_errors' => [],
            'csrf'        => Sessao::csrf(),
        ]);
    }

    public function store(): void
    {
        if (!Sessao::validarCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('Token CSRF inválido.', 'danger');
            return Helpers::redirecionar('/utilizadores/criar');
        }

        $nome   = trim($_POST['nome'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $senha  = $_POST['senha'] ?? '';
        $estado = $_POST['estado'] ?? '1';
        $nivel  = $_POST['nivel'] ?? '1';

        $v = new Validator();

        $v->required('nome', $nome, 'O nome é obrigatório.');
        $v->min('nome', $nome, 3, 'O nome deve ter pelo menos 3 caracteres.');

        $v->required('email', $email, 'O email é obrigatório.');
        $v->email('email', $email, 'O email não é válido.');
        $v->unique('email', $email, Conexao::getInstancia(), 'utilizadores', 'email', 'Já existe um utilizador com este email.');

        $v->required('senha', $senha, 'A senha é obrigatória.');
        $v->min('senha', $senha, 6, 'A senha deve ter pelo menos 6 caracteres.');

        $v->in('estado', $estado, ['0', '1'], 'Estado inválido.');

        if ($v->hasErrors()) {
            echo $this->twig->render('utilizadores/form.twig', [
                'acao'        => 'criar',
                'user'        => null,
                'form_old'    => compact('nome', 'email', 'estado', 'nivel'),
                'form_errors' => $v->getErrors(),
                'csrf'        => Sessao::csrf(),
            ]);
            return;
        }

        $this->userModel->create([
            'nome'   => $nome,
            'email'  => $email,
            'senha'  => password_hash($senha, PASSWORD_DEFAULT),
            'nivel'  => (int) $nivel,
            'estado' => (int) $estado,
        ]);

        Sessao::flash('Utilizador criado com sucesso.', 'success');
        Helpers::redirecionar('/utilizadores');
    }

    public function editar(int $id): void
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            Sessao::flash('Utilizador não encontrado.', 'danger');
            return Helpers::redirecionar('/utilizadores');
        }

        echo $this->twig->render('utilizadores/form.twig', [
            'acao'        => 'editar',
            'user'        => $user,
            'form_old'    => [],
            'form_errors' => [],
            'csrf'        => Sessao::csrf(),
        ]);
    }

    public function update(int $id): void
    {
        if (!Sessao::validarCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('Token CSRF inválido.', 'danger');
            return Helpers::redirecionar('/utilizadores/editar/' . $id);
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            Sessao::flash('Utilizador não encontrado.', 'danger');
            return Helpers::redirecionar('/utilizadores');
        }

        $nome       = trim($_POST['nome'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $estado     = $_POST['estado'] ?? '1';
        $nivel      = $_POST['nivel'] ?? '1';
        $novaSenha  = $_POST['nova_senha'] ?? '';
        $novaSenha2 = $_POST['nova_senha2'] ?? '';

        $v = new Validator();

        $v->required('nome', $nome, 'O nome é obrigatório.');
        $v->min('nome', $nome, 3, 'O nome deve ter pelo menos 3 caracteres.');

        $v->required('email', $email, 'O email é obrigatório.');
        $v->email('email', $email, 'O email não é válido.');
        $v->unique('email', $email, Conexao::getInstancia(), 'utilizadores', 'email', 'Já existe um utilizador com este email.', $user->id);

        $v->in('estado', $estado, ['0', '1'], 'Estado inválido.');

        if ($novaSenha !== '') {
            $v->min('nova_senha', $novaSenha, 6, 'A nova senha deve ter pelo menos 6 caracteres.');
            $v->match('nova_senha', $novaSenha, $novaSenha2, 'As senhas não coincidem.');
        }

        if ($v->hasErrors()) {
            echo $this->twig->render('utilizadores/form.twig', [
                'acao'        => 'editar',
                'user'        => $user,
                'form_old'    => compact('nome', 'email', 'estado', 'nivel'),
                'form_errors' => $v->getErrors(),
                'csrf'        => Sessao::csrf(),
            ]);
            return;
        }

        $dados = [
            'nome'   => $nome,
            'email'  => $email,
            'estado' => (int) $estado,
            'nivel'  => (int) $nivel,
        ];

        if ($novaSenha !== '') {
            $dados['senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $dados);

        Sessao::flash('Utilizador atualizado com sucesso.', 'success');
        Helpers::redirecionar('/utilizadores');
    }

    public function delete(int $id): void
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            Sessao::flash('Utilizador não encontrado.', 'danger');
            return Helpers::redirecionar('/utilizadores');
        }

        $this->userModel->delete($id);

        Sessao::flash('Utilizador eliminado com sucesso.', 'success');
        Helpers::redirecionar('/utilizadores');
    }
}