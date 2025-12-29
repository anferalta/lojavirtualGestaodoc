<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use App\Core\Perfil;
use App\Core\Permission;
use App\Core\Validator;
use App\Core\Sessao;
use App\Core\Helpers;

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
            'flash'  => Sessao::flash()
        ]);
    }

    public function criar(): void
    {
        echo $this->twig->render('perfis/criar.twig', [
            'permissoes' => $this->permModel->all(),
            'csrf'       => Sessao::csrf()
        ]);
    }

    public function store(): void
    {
        $nome       = $_POST['nome'] ?? '';
        $descricao  = $_POST['descricao'] ?? '';
        $estado     = $_POST['estado'] ?? 'ativo';
        $permissoes = $_POST['permissoes'] ?? [];

        $validator = new Validator();
        $validator->required('nome', $nome, 'O nome é obrigatório.');

        if ($validator->hasErrors()) {
            echo $this->twig->render('perfis/criar.twig', [
                'errors'     => $validator->getErrors(),
                'permissoes' => $this->permModel->all(),
                'csrf'       => Sessao::csrf()
            ]);
            return;
        }

        $this->perfilModel->create([
            'nome'      => $nome,
            'descricao' => $descricao,
            'estado'    => $estado
        ]);

        $perfilId = Conexao::getInstancia()->lastInsertId();
        $this->perfilModel->syncPermissions($perfilId, $permissoes);

        Sessao::setFlash('Perfil criado com sucesso.', 'success');
        Helpers::redirecionar('/perfis');
    }

    public function editar(int $id): void
    {
        $perfil = $this->perfilModel->find($id);

        if (!$perfil) {
            Sessao::setFlash('Perfil não encontrado.', 'danger');
            Helpers::redirecionar('/perfis');
            return;
        }

        echo $this->twig->render('perfis/editar.twig', [
            'perfil'         => $perfil,
            'permissoes'     => $this->permModel->all(),
            'permissoesAtivas' => array_column($this->perfilModel->getPermissions($id), 'id'),
            'csrf'           => Sessao::csrf()
        ]);
    }

    public function update(int $id): void
    {
        $perfil = $this->perfilModel->find($id);

        if (!$perfil) {
            Sessao::setFlash('Perfil não encontrado.', 'danger');
            Helpers::redirecionar('/perfis');
            return;
        }

        $nome       = $_POST['nome'] ?? '';
        $descricao  = $_POST['descricao'] ?? '';
        $estado     = $_POST['estado'] ?? 'ativo';
        $permissoes = $_POST['permissoes'] ?? [];

        $validator = new Validator();
        $validator->required('nome', $nome, 'O nome é obrigatório.');

        if ($validator->hasErrors()) {
            echo $this->twig->render('perfis/editar.twig', [
                'perfil'         => $perfil,
                'errors'         => $validator->getErrors(),
                'permissoes'     => $this->permModel->all(),
                'permissoesAtivas' => array_column($this->perfilModel->getPermissions($id), 'id'),
                'csrf'           => Sessao::csrf()
            ]);
            return;
        }

        $this->perfilModel->update($id, [
            'nome'      => $nome,
            'descricao' => $descricao,
            'estado'    => $estado
        ]);

        $this->perfilModel->syncPermissions($id, $permissoes);

        Sessao::setFlash('Perfil atualizado com sucesso.', 'success');
        Helpers::redirecionar('/perfis');
    }

    public function delete(int $id): void
    {
        $perfil = $this->perfilModel->find($id);

        if (!$perfil) {
            Sessao::setFlash('Perfil não encontrado.', 'danger');
            Helpers::redirecionar('/perfis');
            return;
        }

        $this->perfilModel->delete($id);

        Sessao::setFlash('Perfil eliminado com sucesso.', 'success');
        Helpers::redirecionar('/perfis');
    }
}