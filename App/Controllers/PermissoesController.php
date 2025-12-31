<?php

namespace app\Controllers;

use app\Core\BaseController;
use app\Core\Conexao;
use app\Core\Permission;
use app\Core\Validator;
use app\Core\Sessao;
use app\Core\Helpers;

class PermissoesController extends BaseController
{
    private Permission $permModel;

    public function __construct()
    {
        parent::__construct();
        $this->permModel = new Permission(Conexao::getInstancia());
    }

    public function index(): void
    {
        echo $this->twig->render('permissoes/index.twig', [
            'permissoes' => $this->permModel->all(),
            'csrf'       => Sessao::csrf(),
        ]);
    }

    public function criar(): void
    {
        echo $this->twig->render('permissoes/form.twig', [
            'acao'        => 'criar',
            'permissao'   => null,
            'form_old'    => [],
            'form_errors' => [],
            'csrf'        => Sessao::csrf(),
        ]);
    }

    public function store(): void
    {
        if (!Sessao::validarCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('Token CSRF inválido.', 'danger');
            return Helpers::redirecionar('/permissoes/criar');
        }

        $chave     = trim($_POST['chave'] ?? '');
        $nome      = trim($_POST['nome'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');

        $v = new Validator();
        $v->required('chave', $chave, 'A chave é obrigatória.');
        $v->required('nome', $nome, 'O nome é obrigatório.');
        $v->min('chave', $chave, 3, 'A chave deve ter pelo menos 3 caracteres.');

        if ($v->hasErrors()) {
            echo $this->twig->render('permissoes/form.twig', [
                'acao'        => 'criar',
                'permissao'   => null,
                'form_old'    => compact('chave', 'nome', 'categoria'),
                'form_errors' => $v->getErrors(),
                'csrf'        => Sessao::csrf(),
            ]);
            return;
        }

        $this->permModel->create([
            'chave'     => $chave,
            'nome'      => $nome,
            'categoria' => $categoria,
        ]);

        Sessao::flash('Permissão criada com sucesso.', 'success');
        Helpers::redirecionar('/permissoes');
    }

    public function editar(int $id): void
    {
        $permissao = $this->permModel->find($id);

        if (!$permissao) {
            Sessao::flash('Permissão não encontrada.', 'danger');
            return Helpers::redirecionar('/permissoes');
        }

        echo $this->twig->render('permissoes/form.twig', [
            'acao'        => 'editar',
            'permissao'   => $permissao,
            'form_old'    => [],
            'form_errors' => [],
            'csrf'        => Sessao::csrf(),
        ]);
    }

    public function update(int $id): void
    {
        if (!Sessao::validarCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('Token CSRF inválido.', 'danger');
            return Helpers::redirecionar('/permissoes/editar/' . $id);
        }

        $permissao = $this->permModel->find($id);

        if (!$permissao) {
            Sessao::flash('Permissão não encontrada.', 'danger');
            return Helpers::redirecionar('/permissoes');
        }

        $chave     = trim($_POST['chave'] ?? '');
        $nome      = trim($_POST['nome'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');

        $v = new Validator();
        $v->required('chave', $chave, 'A chave é obrigatória.');
        $v->required('nome', $nome, 'O nome é obrigatório.');
        $v->min('chave', $chave, 3, 'A chave deve ter pelo menos 3 caracteres.');

        if ($v->hasErrors()) {
            echo $this->twig->render('permissoes/form.twig', [
                'acao'        => 'editar',
                'permissao'   => $permissao,
                'form_old'    => compact('chave', 'nome', 'categoria'),
                'form_errors' => $v->getErrors(),
                'csrf'        => Sessao::csrf(),
            ]);
            return;
        }

        $this->permModel->update($id, [
            'chave'     => $chave,
            'nome'      => $nome,
            'categoria' => $categoria,
        ]);

        Sessao::flash('Permissão atualizada com sucesso.', 'success');
        Helpers::redirecionar('/permissoes');
    }

    public function delete(int $id): void
    {
        $permissao = $this->permModel->find($id);

        if (!$permissao) {
            Sessao::flash('Permissão não encontrada.', 'danger');
            return Helpers::redirecionar('/permissoes');
        }

        $this->permModel->delete($id);

        Sessao::flash('Permissão eliminada com sucesso.', 'success');
        Helpers::redirecionar('/permissoes');
    }
}