<?php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Sessao;
use App\Core\Helpers;
use App\Core\ACL;
use PDO;

class PerfisAdminController extends BaseController {

    private PDO $db;
    private ACL $acl;

    public function __construct() {
        parent::__construct();

        // Ligação PDO correta
        $this->db = \App\Core\Database::getConexao();

        $this->acl = new ACL();
    }

    public function index(): void {
        if (!$this->acl->can('admin.perfis.ver')) {
            (new \App\Controllers\ErrorController())->error403();
            return;
        }

        $stmt = $this->db->query("SELECT * FROM perfis ORDER BY nome");
        $perfis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/perfis/index', [
            'perfis' => $perfis
        ]);
    }

    public function criar(): void {
        if (!$this->acl->can('admin.perfis.criar')) {
            (new \App\Controllers\ErrorController())->error403();
            return;
        }

        $stmt = $this->db->query("SELECT * FROM permissoes ORDER BY chave");
        $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/perfis/criar', [
            'permissoes' => $permissoes
        ]);
    }

    public function store(): void {
        if (!$this->acl->can('admin.perfis.criar')) {
            (new \App\Controllers\ErrorController())->error403();
            return;
        }

        // CSRF
        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validaCsrf($token)) {
            Helpers::flash('erro', 'Token CSRF inválido.');
            Helpers::redirect('/admin/perfis');
        }

        $nome = trim($_POST['nome'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $permissoes_ids = $_POST['permissoes'] ?? [];

        if ($nome === '' || $slug === '') {
            Helpers::flash('erro', 'Nome e slug são obrigatórios.');
            Helpers::redirect('/admin/perfis/criar');
        }

        $stmt = $this->db->prepare("
            INSERT INTO perfis (nome, slug, descricao)
            VALUES (:nome, :slug, :descricao)
        ");
        $stmt->execute([
            ':nome' => $nome,
            ':slug' => $slug,
            ':descricao' => $descricao ?: null,
        ]);

        $perfil_id = (int) $this->db->lastInsertId();

        if (!empty($permissoes_ids)) {
            $stmtPivot = $this->db->prepare("
                INSERT INTO perfil_permissao (perfil_id, permissao_id)
                VALUES (:perfil_id, :permissao_id)
            ");
            foreach ($permissoes_ids as $pid) {
                $stmtPivot->execute([
                    ':perfil_id' => $perfil_id,
                    ':permissao_id' => (int) $pid
                ]);
            }
        }

        Helpers::flash('sucesso', 'Perfil criado com sucesso.');
        Helpers::redirect('/admin/perfis');
    }

    public function editar(int $id): void {
        if (!$this->acl->can('admin.perfis.editar')) {
            (new \App\Controllers\ErrorController())->error403();
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM perfis WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$perfil) {
            (new \App\Controllers\ErrorController())->error404();
            return;
        }

        $stmt = $this->db->query("SELECT * FROM permissoes ORDER BY chave");
        $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
            SELECT permissao_id FROM perfil_permissao WHERE perfil_id = :id
        ");
        $stmt->execute([':id' => $id]);
        $perfilPerms = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->view('admin/perfis/editar', [
            'perfil' => $perfil,
            'permissoes' => $permissoes,
            'perfilPerms' => $perfilPerms
        ]);
    }

    public function update(int $id): void {
        if (!$this->acl->can('admin.perfis.editar')) {
            (new \App\Controllers\ErrorController())->error403();
            return;
        }

        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validaCsrf($token)) {
            Helpers::flash('erro', 'Token CSRF inválido.');
            Helpers::redirect('/admin/perfis');
        }

        $nome = trim($_POST['nome'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $permissoes_ids = $_POST['permissoes'] ?? [];

        if ($nome === '' || $slug === '') {
            Helpers::flash('erro', 'Nome e slug são obrigatórios.');
            Helpers::redirect("/admin/perfis/editar/{$id}");
        }

        $stmt = $this->db->prepare("
            UPDATE perfis 
            SET nome = :nome, slug = :slug, descricao = :descricao
            WHERE id = :id
        ");
        $stmt->execute([
            ':nome' => $nome,
            ':slug' => $slug,
            ':descricao' => $descricao ?: null,
            ':id' => $id
        ]);

        $this->db->prepare("
            DELETE FROM perfil_permissao WHERE perfil_id = :id
        ")->execute([':id' => $id]);

        if (!empty($permissoes_ids)) {
            $stmtPivot = $this->db->prepare("
                INSERT INTO perfil_permissao (perfil_id, permissao_id)
                VALUES (:perfil_id, :permissao_id)
            ");
            foreach ($permissoes_ids as $pid) {
                $stmtPivot->execute([
                    ':perfil_id' => $id,
                    ':permissao_id' => (int) $pid
                ]);
            }
        }

        Helpers::flash('sucesso', 'Perfil atualizado com sucesso.');
        Helpers::redirect('/admin/perfis');
    }
}
