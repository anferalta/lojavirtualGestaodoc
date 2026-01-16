<?php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Helpers;
use App\Core\Sessao;
use App\Models\Perfil;

class PerfisAdminController extends BaseController
{
    public function index()
    {
        $perfis = (new Perfil())->all();
        return $this->view('admin/perfis/index', compact('perfis'));
    }

    public function criar()
    {
        return $this->view('admin/perfis/criar');
    }

    public function criarSubmit()
    {
        $dados = $_POST;

        (new Perfil())->insert($dados);

        Sessao::flash('sucesso', 'Perfil criado com sucesso.');
        Helpers::redirect('admin/perfis');
    }

    public function editar($id)
    {
        $perfil = (new Perfil())->find($id);
        return $this->view('admin/perfis/editar', compact('perfil'));
    }

    public function editarSubmit($id)
    {
        $dados = $_POST;

        (new Perfil())->update($dados, "id = :id", [':id' => $id]);

        Sessao::flash('sucesso', 'Perfil atualizado.');
        Helpers::redirect('admin/perfis');
    }

    public function apagar($id)
    {
        (new Perfil())->delete($id);

        Sessao::flash('sucesso', 'Perfil removido.');
        Helpers::redirect('admin/perfis');
    }
}