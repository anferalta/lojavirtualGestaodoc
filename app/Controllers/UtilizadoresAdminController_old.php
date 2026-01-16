<?php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Sessao;
use App\Core\Helpers;
use App\Core\ACL;
use App\Models\Utilizador;
use App\Models\Perfil;
use App\Models\UtilizadorPerfil;

class UtilizadoresAdminController extends BaseController
{
    private ACL $acl;

    public function __construct()
    {
        parent::__construct();
        $this->acl = new ACL();
    }

    /* ============================================================
     * LISTAGEM
     * ============================================================ */
    public function index(): void
    {
        // ACL opcional
        // if (!$this->acl->can('utilizadores.ver')) {
        //     (new \App\Controllers\ErrorController())->error403();
        //     return;
        // }

        $utilizadores = (new Utilizador())
            ->orderBy('id', 'DESC')
            ->all();

        $this->view('admin/utilizadores/index', [
            'utilizadores' => $utilizadores
        ]);
    }

    /* ============================================================
     * FORMULÁRIO DE CRIAÇÃO
     * ============================================================ */
    public function create(): void
    {
        $perfis = (new Perfil())->all();

        $this->view('admin/utilizadores/create', [
            'perfis' => $perfis
        ]);
    }

    /* ============================================================
     * GRAVAR NOVO UTILIZADOR
     * ============================================================ */
    public function store(): void
    {
        // CSRF
        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validaCsrf($token)) {
            Helpers::flash('erro', 'Token CSRF inválido.');
            Helpers::redirect('/admin/utilizadores');
        }

        $dados = [
            'nome'  => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'estado' => 1
        ];

        $perfilId = $_POST['perfil_id'] ?? null;

        if ($dados['nome'] === '' || $dados['email'] === '') {
            Helpers::flash('erro', 'Nome e email são obrigatórios.');
            Helpers::redirect('/admin/utilizadores/criar');
        }

        $utilizadorModel = new Utilizador();
        $id = $utilizadorModel->create($dados);

        if ($perfilId) {
            (new UtilizadorPerfil())->create([
                'utilizador_id' => $id,
                'perfil_id' => $perfilId
            ]);
        }

        Helpers::flash('sucesso', 'Utilizador criado com sucesso.');
        Helpers::redirect('/admin/utilizadores');
    }

    /* ============================================================
     * FORMULÁRIO DE EDIÇÃO
     * ============================================================ */
    public function edit(int $id): void
    {
        $utilizador = (new Utilizador())->find($id);
        $perfis = (new Perfil())->all();
        $perfilAtual = (new UtilizadorPerfil())->getPerfilIdByUser($id);

        if (!$utilizador) {
            Helpers::redirect('/404');
        }

        $this->view('admin/utilizadores/edit', [
            'utilizador' => $utilizador,
            'perfis' => $perfis,
            'perfilAtual' => $perfilAtual
        ]);
    }

    /* ============================================================
     * ATUALIZAR UTILIZADOR
     * ============================================================ */
    public function update(int $id): void
    {
        // CSRF
        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validaCsrf($token)) {
            Helpers::flash('erro', 'Token CSRF inválido.');
            Helpers::redirect('/admin/utilizadores');
        }

        $dados = [
            'nome'  => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'estado' => isset($_POST['estado']) ? 1 : 0
        ];

        $perfilId = $_POST['perfil_id'] ?? null;

        (new Utilizador())->updateUser($id, $dados);

        $upModel = new UtilizadorPerfil();
        $upModel->deleteWhere('utilizador_id', $id);

        if ($perfilId) {
            $upModel->create([
                'utilizador_id' => $id,
                'perfil_id' => $perfilId
            ]);
        }

        Helpers::flash('sucesso', 'Utilizador atualizado com sucesso.');
        Helpers::redirect('/admin/utilizadores');
    }

    /* ============================================================
     * ELIMINAR UTILIZADOR
     * ============================================================ */
    public function delete(int $id): void
    {
        (new UtilizadorPerfil())->deleteWhere('utilizador_id', $id);
        (new Utilizador())->deleteUser($id);

        Helpers::flash('sucesso', 'Utilizador eliminado com sucesso.');
        Helpers::redirect('/admin/utilizadores');
    }
}