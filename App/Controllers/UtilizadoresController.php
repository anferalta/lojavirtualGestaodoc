<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Utilizador;
use App\Models\Perfil;

class UtilizadoresController extends BaseController
{
    public function index(): void
    {
        $utilizadores = (new Utilizador())->all();
        $this->view('utilizadores/index', compact('utilizadores'));
    }

    public function create(): void
    {
        $perfis = (new Perfil())->all();
        $this->view('utilizadores/create', compact('perfis'));
    }

    public function store(): void
    {
        $dados = [
            'nome' => trim($_POST['nome']),
            'email' => trim($_POST['email']),
            'perfil_id' => $_POST['perfil_id'],
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
        ];

        (new Utilizador())->insert($dados);

        flash('success', 'Utilizador criado.');
        redirect('/utilizadores');
    }

    public function edit(int $id): void
    {
        $utilizador = (new Utilizador())->find($id);
        $perfis = (new Perfil())->all();

        $this->view('utilizadores/edit', compact('utilizador', 'perfis'));
    }

    public function update(int $id): void
    {
        $dados = [
            'nome' => trim($_POST['nome']),
            'email' => trim($_POST['email']),
            'perfil_id' => $_POST['perfil_id'],
        ];

        if (!empty($_POST['password'])) {
            $dados['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        (new Utilizador())->update($dados, "id = :id", [':id' => $id]);

        flash('success', 'Utilizador atualizado.');
        redirect('/utilizadores');
    }

    public function delete(int $id): void
    {
        (new Utilizador())->delete($id);

        flash('success', 'Utilizador eliminado.');
        redirect('/utilizadores');
    }
}