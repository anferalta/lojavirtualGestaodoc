<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Permissao;

class PermissoesController extends BaseController
{
    public function index(): void
    {
        $permissoes = (new Permissao())->all();
        $this->view('permissoes/index', compact('permissoes'));
    }

    public function create(): void
    {
        $this->view('permissoes/create');
    }

    public function store(): void
    {
        $dados = [
            'chave' => trim($_POST['chave']),
            'descricao' => trim($_POST['descricao'])
        ];

        (new Permissao())->insert($dados);

        flash('success', 'Permissão criada com sucesso.');
        redirect('/permissoes');
    }

    public function edit(int $id): void
    {
        $permissao = (new Permissao())->find($id);
        $this->view('permissoes/edit', compact('permissao'));
    }

    public function update(int $id): void
    {
        $dados = [
            'chave' => trim($_POST['chave']),
            'descricao' => trim($_POST['descricao'])
        ];

        (new Permissao())->update($dados, "id = :id", [':id' => $id]);

        flash('success', 'Permissão atualizada.');
        redirect('/permissoes');
    }

    public function delete(int $id): void
    {
        (new Permissao())->delete($id);

        flash('success', 'Permissão eliminada.');
        redirect('/permissoes');
    }
}