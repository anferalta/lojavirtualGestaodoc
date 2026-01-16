<?php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Helpers;
use App\Core\Sessao;
use App\Models\Utilizador;
use App\Models\Perfil;

class UtilizadoresAdminController extends BaseController
{
    public function index()
    {
        $pagina = $_GET['p'] ?? 1;

        $utilizadores = (new Utilizador())
            ->orderBy('id', 'DESC')
            ->paginate(10, $pagina);

        $total = Utilizador::count();
        $total_paginas = ceil($total / 10);

        return $this->view('admin/utilizadores/index', [
            'utilizadores' => $utilizadores,
            'pagina' => $pagina,
            'total_paginas' => $total_paginas
        ]);
    }

    public function criar()
    {
        $perfis = (new Perfil())->all();
        return $this->view('admin/utilizadores/criar', compact('perfis'));
    }

    public function criarSubmit()
    {
        $dados = $_POST;

        // verificar email duplicado
        if ((new Utilizador())->findBy('email', $dados['email'])) {
            Sessao::flash('erro', 'O email já está registado.');
            Helpers::redirect('admin/utilizadores/criar');
            return;
        }

        $dados['estado'] = 1;

        (new Utilizador())->create($dados);

        Sessao::flash('sucesso', 'Utilizador criado com sucesso.');
        Helpers::redirect('admin/utilizadores');
    }

    public function editar($id)
    {
        $utilizador = (new Utilizador())->find($id);
        $perfis = (new Perfil())->all();

        return $this->view('admin/utilizadores/editar', compact('utilizador', 'perfis'));
    }

    public function editarSubmit($id)
    {
        $dados = $_POST;

        (new Utilizador())->updateUser($id, $dados);

        Sessao::flash('sucesso', 'Utilizador atualizado.');
        Helpers::redirect('admin/utilizadores');
    }

    public function apagar($id)
    {
        (new Utilizador())->deleteUser($id);

        Sessao::flash('sucesso', 'Utilizador removido.');
        Helpers::redirect('admin/utilizadores');
    }
}