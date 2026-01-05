<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Utilizador;
use App\Models\Perfil;
use App\Models\UtilizadorPerfil;

class UtilizadoresAdminController extends BaseController
{
    public function index(): void
    {
        $utilizadores = (new Utilizador())->allWithPerfis();

        $this->view('utilizadores/index', [
            'utilizadores' => $utilizadores
        ]);
    }

    public function create(): void
    {
        $perfis = (new Perfil())->all();

        $this->view('utilizadores/create', [
            'perfis' => $perfis
        ]);
    }

    public function store(): void
    {
        $dados = [
            'nome'  => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'estado' => 1
        ];

        $perfilId = $_POST['perfil_id'] ?? null;

        if ($dados['nome'] === '' || $dados['email'] === '') {
            flash('error', 'Nome e email são obrigatórios.');
            redirect('/admin/utilizadores/criar');
        }

        $utilizadorModel = new Utilizador();
        $id = $utilizadorModel->create($dados);

        if ($perfilId) {
            (new UtilizadorPerfil())->create([
                'utilizador_id' => $id,
                'perfil_id' => $perfilId
            ]);
        }

        flash('success', 'Utilizador criado com sucesso.');
        redirect('/admin/utilizadores');
    }

    public function edit(int $id): void
    {
        $utilizador = (new Utilizador())->find($id);
        $perfis = (new Perfil())->all();
        $perfilAtual = (new UtilizadorPerfil())->getPerfilIdByUser($id);

        if (!$utilizador) {
            redirect('/404');
        }

        $this->view('utilizadores/edit', [
            'utilizador' => $utilizador,
            'perfis' => $perfis,
            'perfilAtual' => $perfilAtual
        ]);
    }

    public function update(int $id): void
    {
        $dados = [
            'nome'  => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'estado' => isset($_POST['estado']) ? 1 : 0
        ];

        $perfilId = $_POST['perfil_id'] ?? null;

        (new Utilizador())->update($id, $dados);

        $upModel = new UtilizadorPerfil();
        $upModel->deleteWhere('utilizador_id', $id);

        if ($perfilId) {
            $upModel->create([
                'utilizador_id' => $id,
                'perfil_id' => $perfilId
            ]);
        }

        flash('success', 'Utilizador atualizado com sucesso.');
        redirect('/admin/utilizadores');
    }

    public function delete(int $id): void
    {
        (new UtilizadorPerfil())->deleteWhere('utilizador_id', $id);
        (new Utilizador())->delete($id);

        flash('success', 'Utilizador eliminado com sucesso.');
        redirect('/admin/utilizadores');
    }
}