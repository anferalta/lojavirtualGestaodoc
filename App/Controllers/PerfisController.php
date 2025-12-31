<?php

namespace app\Controllers;

use app\Core\BaseController;
use app\Core\Conexao;
use app\Core\Perfil;
use app\Core\Permission;
use app\Core\Validator;
use app\Core\Sessao;
use app\Core\Helpers;

class PerfisController extends BaseController
{
    private Perfil $perfilModel;
    private Permission $permModel;

    public function __construct()
    {
        parent::__construct();
        $this->perfilModel = new Perfil(Conexao::getInstancia());
        $this->permModel   = new Permission(Conexao::getInstancia());
    }

    public function index(): void
    {
        echo $this->twig->render('perfis/index.twig', [
            'perfis' => $this->perfilModel->all(),
            'csrf'   => Sessao::csrf(),
        ]);
    }

    public function criar(): void
    {
        echo $this->twig->render('perfis/form.twig', [
            'acao'            => 'criar',
            'perfil'          => null,
            'permissoes'      => $this->permModel->all(),
            'permissoesAtivas'=> [],
            'form_errors'     => [],
            'csrf'            => Sessao::csrf(),
        ]);
    }

    public function store(): void
    {
        if (!Sessao::validarCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('Token CSRF inválido.', 'danger');
            return Helpers::redirecionar('/perfis/criar');
        }

        $nome       = trim($_POST['nome'] ?? '');
        $descricao  = trim($_POST['descricao'] ?? '');
        $estado     = $_POST['estado'] ?? 'ativo';
        $permissoes = $_POST['permissoes'] ?? [];

        $v = new Validator();
        $v->required('nome', $nome, 'O nome é obrigatório.');
        $v->min('nome', $nome, 3, 'O nome deve ter pelo menos 3 caracteres.');
        $v->in('estado', $estado, ['ativo', 'inativo'], 'Estado inválido.');

        if ($v->hasErrors()) {
            echo $this->twig->render('perfis/form.twig', [
                'acao'            => 'criar',
                'perfil'          => null,
                'permissoes'      => $this->permModel->all(),
                'permissoesAtivas'=> $permissoes,
                'form_errors'     => $v->getErrors(),
                'csrf'            => Sessao::csrf(),
            ]);
            return;
        }

        $this->perfilModel->create([
            'nome'      => $nome,
            'descricao' => $descricao,
            'estado'    => $estado,
        ]);

        $perfilId = Conexao::getInstancia()->lastInsertId();
        $this->perfilModel->syncPermissions($perfilId, array_map('intval', $permissoes));

        Sessao::flash('Perfil criado com sucesso.', 'success');
        Helpers::redirecionar('/perfis');
    }

    public function editar(int $id): void
    {
        $perfil = $this->perfilModel->find($id);

        if (!$perfil) {
            Sessao::flash('Perfil não encontrado.', 'danger');
            return Helpers::redirecionar('/perfis');
        }

        echo $this->twig->render('perfis/form.twig', [
            'acao'            => 'editar',
            'perfil'          => $perfil,
            'permissoes'      => $this->permModel->all(),
            'permissoesAtivas'=> $this->perfilModel->getPermissionIds($id),
            'form_errors'     => [],
            'csrf'            => Sessao::csrf(),
        ]);
    }

    public function update(int $id): void
    {
        if (!Sessao::validarCsrf($_POST['_csrf'] ?? '')) {
            Sessao::flash('Token CSRF inválido.', 'danger');
            return Helpers::redirecionar('/perfis/editar/' . $id);
        }

        $perfil = $this->perfilModel->find($id);

        if (!$perfil) {
            Sessao::flash('Perfil não encontrado.', 'danger');
            return Helpers::redirecionar('/perfis');
        }

        $nome       = trim($_POST['nome'] ?? '');
        $descricao  = trim($_POST['descricao'] ?? '');
        $estado     = $_POST['estado'] ?? 'ativo';
        $permissoes = $_POST['permissoes'] ?? [];

        $v = new Validator();
        $v->required('nome', $nome, 'O nome é obrigatório.');
        $v->min('nome', $nome, 3, 'O nome deve ter pelo menos 3 caracteres.');
        $v->in('estado', $estado, ['ativo', 'inativo'], 'Estado inválido.');

        if ($v->hasErrors()) {
            echo $this->twig->render('perfis/form.twig', [
                'acao'            => 'editar',
                'perfil'          => $perfil,
                'permissoes'      => $this->permModel->all(),
                'permissoesAtivas'=> $permissoes,
                'form_errors'     => $v->getErrors(),
                'csrf'            => Sessao::csrf(),
            ]);
            return;
        }

        $this->perfilModel->update($id, [
            'nome'      => $nome,
            'descricao' => $descricao,
            'estado'    => $estado,
        ]);

        $this->perfilModel->syncPermissions($id, array_map('intval', $permissoes));

        Sessao::flash('Perfil atualizado com sucesso.', 'success');
        Helpers::redirecionar('/perfis');
    }

    public function delete(int $id): void
    {
        $perfil = $this->perfilModel->find($id);

        if (!$perfil) {
            Sessao::flash('Perfil não encontrado.', 'danger');
            return Helpers::redirecionar('/perfis');
        }

        $this->perfilModel->delete($id);

        Sessao::flash('Perfil eliminado com sucesso.', 'success');
        Helpers::redirecionar('/perfis');
    }
}