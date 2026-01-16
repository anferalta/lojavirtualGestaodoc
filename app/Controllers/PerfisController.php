<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Perfil;

class PerfisController extends BaseController {

    public function index(): void {
        $perfis = (new Perfil())->all();

        $this->view('perfis/index', [
            'perfis' => $perfis
        ]);
    }

    public function create(): void {
        $this->view('perfis/create');
    }

    public function store(): void {
        $nome = trim($_POST['nome'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        if ($nome === '' || $slug === '') {
            flash('error', 'Nome e slug são obrigatórios.');
            redirect('/perfis/criar');
        }

        (new Perfil())->insert([
            'nome' => $nome,
            'slug' => $slug,
            'descricao' => $descricao
        ]);

        flash('success', 'Perfil criado com sucesso.');
        redirect('/perfis');
    }

    public function edit(int $id): void {
        $perfil = (new Perfil())->find($id);

        if (!$perfil) {
            flash('error', 'Perfil não encontrado.');
            redirect('/perfis');
        }

        $this->view('perfis/edit', [
            'perfil' => $perfil
        ]);
    }

    public function update(int $id): void {
        $nome = trim($_POST['nome'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        if ($nome === '' || $slug === '') {
            flash('error', 'Nome e slug são obrigatórios.');
            redirect("/perfis/editar/{$id}");
        }

        (new Perfil())->update([
            'nome' => $nome,
            'slug' => $slug,
            'descricao' => $descricao
                ], "id = :id", [':id' => $id]);

        flash('success', 'Perfil atualizado com sucesso.');
        redirect('/perfis');
    }

    public function delete(int $id): void {
        (new Perfil())->delete($id);

        flash('success', 'Perfil eliminado com sucesso.');
        redirect('/perfis');
    }

    public function permissions(int $id): void {
        $perfil = (new Perfil())->find($id);
        $permissoes = (new Permissao())->all();
        $atribuicoes = (new PerfilPermissao())->allGrouped();

        $permissoesDoPerfil = $atribuicoes[$id] ?? [];

        $this->view('perfis/permissoes', compact('perfil', 'permissoes', 'permissoesDoPerfil'));
    }

    public function savePermissions(int $id): void {
        $ids = $_POST['permissoes'] ?? [];

        (new PerfilPermissao())->sync($id, $ids);

        flash('success', 'Permissões atualizadas.');
        redirect("/perfis/permissoes/{$id}");
    }
}
