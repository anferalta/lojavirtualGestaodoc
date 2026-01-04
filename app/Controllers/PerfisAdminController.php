<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Perfil;

class PerfisAdminController extends BaseController
{
    public function index(): void
    {
        $perfis = (new Perfil())->all();

        $this->view('perfis/index', [
            'perfis' => $perfis
        ]);
    }

    public function create(): void
    {
        $this->view('perfis/create');
    }

    public function store(): void
    {
        $nome = trim($_POST['nome'] ?? '');

        if ($nome === '') {
            flash('error', 'O nome do perfil é obrigatório.');
            redirect('/admin/perfis/criar');
        }

        (new Perfil())->create([
            'nome' => $nome
        ]);

        flash('success', 'Perfil criado com sucesso.');
        redirect('/admin/perfis');
    }

    public function edit(int $id): void
    {
        $perfil = (new Perfil())->find($id);

        if (!$perfil) {
            redirect('/404');
        }

        $this->view('perfis/edit', [
            'perfil' => $perfil
        ]);
    }

    public function update(int $id): void
    {
        $nome = trim($_POST['nome'] ?? '');

        if ($nome === '') {
            flash('error', 'O nome do perfil é obrigatório.');
            redirect("/admin/perfis/editar/{$id}");
        }

        (new Perfil())->update($id, [
            'nome' => $nome
        ]);

        flash('success', 'Perfil atualizado com sucesso.');
        redirect('/admin/perfis');
    }

    public function delete(int $id): void
    {
        (new Perfil())->delete($id);

        flash('success', 'Perfil eliminado com sucesso.');
        redirect('/admin/perfis');
    }
}