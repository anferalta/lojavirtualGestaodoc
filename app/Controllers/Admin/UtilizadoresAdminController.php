<?php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Models\Utilizador;
use App\Models\Perfil;
use App\Core\Helpers;
use App\Core\Sessao;

class UtilizadoresAdminController extends BaseController
{
    public function index()
    {
        $pagina = $_GET['p'] ?? 1;
        $nome = $_GET['nome'] ?? null;
        $email = $_GET['email'] ?? null;
        $estado = $_GET['estado'] ?? null;

        $query = new Utilizador();

        if ($nome) {
            $query->where('nome', 'LIKE', "%$nome%");
        }

        if ($email) {
            $query->where('email', 'LIKE', "%$email%");
        }

        if ($estado !== null && $estado !== '') {
            $query->where('estado', '=', $estado);
        }

        $utilizadores = $query
            ->orderBy('id', 'DESC')
            ->paginate(10, $pagina);

        $total = Utilizador::count();
        $total_paginas = ceil($total / 10);

        return $this->view('admin/utilizadores/index', [
            'utilizadores' => $utilizadores,
            'pagina' => $pagina,
            'total_paginas' => $total_paginas,
            'nome' => $nome,
            'email' => $email,
            'estado' => $estado
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

        $dados['estado'] = 1;

        $utilizador = new Utilizador();
        $id = $utilizador->create($dados);

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