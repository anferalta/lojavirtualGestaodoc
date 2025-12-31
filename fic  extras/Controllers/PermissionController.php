<?php
namespace app\Controllers;

use app\Core\BaseController;
use app\Core\Conexao;
use app\Core\Helpers;

class PermissionController extends BaseController
{
    public function index(): void
    {
        $db = Conexao::getInstancia();
        $permissoes = $db->query("SELECT * FROM permissoes ORDER BY codigo")->fetchAll();

        echo $this->twig->render('permissoes/index.twig', [
            'titulo' => 'Gestão de Permissões',
            'permissoes' => $permissoes
        ]);
    }

    public function criar(): void
    {
        echo $this->twig->render('permissoes/criar.twig', [
            'titulo' => 'Criar Permissão'
        ]);
    }

    public function guardar(): void
    {
        $db = Conexao::getInstancia();

        $codigo = trim($_POST['codigo']);
        $descricao = trim($_POST['descricao']);

        if ($codigo === '') {
            $this->sessao->setFlash("O código da permissão é obrigatório", "danger");
            header("Location: " . Helpers::url('/permissoes/criar'));
            exit;
        }

        $stm = $db->prepare("INSERT INTO permissoes (codigo, descricao) VALUES (:codigo, :descricao)");
        $stm->execute([
            'codigo' => $codigo,
            'descricao' => $descricao
        ]);

        Helpers::log("Criou permissão $codigo", 'permissoes');

        $this->sessao->setFlash("Permissão criada com sucesso", "success");
        header("Location: " . Helpers::url('/permissoes'));
        exit;
    }

    public function editar(int $id): void
    {
        $db = Conexao::getInstancia();

        $stm = $db->prepare("SELECT * FROM permissoes WHERE id = :id");
        $stm->execute(['id' => $id]);
        $perm = $stm->fetch();

        if (!$perm) {
            $this->sessao->setFlash("Permissão não encontrada", "danger");
            header("Location: " . Helpers::url('/permissoes'));
            exit;
        }

        echo $this->twig->render('permissoes/editar.twig', [
            'titulo' => 'Editar Permissão',
            'perm' => $perm
        ]);
    }

    public function atualizar(int $id): void
    {
        $db = Conexao::getInstancia();

        $codigo = trim($_POST['codigo']);
        $descricao = trim($_POST['descricao']);

        if ($codigo === '') {
            $this->sessao->setFlash("O código da permissão é obrigatório", "danger");
            header("Location: " . Helpers::url("/permissoes/editar/$id"));
            exit;
        }

        $stm = $db->prepare("UPDATE permissoes SET codigo = :codigo, descricao = :descricao WHERE id = :id");
        $stm->execute([
            'codigo' => $codigo,
            'descricao' => $descricao,
            'id' => $id
        ]);

        Helpers::log("Atualizou permissão ID $id", 'permissoes');

        $this->sessao->setFlash("Permissão atualizada com sucesso", "success");
        header("Location: " . Helpers::url('/permissoes'));
        exit;
    }

    public function eliminar(int $id): void
    {
        if ($id == 1) {
            $this->sessao->setFlash("Não é possível eliminar a permissão principal", "danger");
            header("Location: " . Helpers::url('/permissoes'));
            exit;
        }

        $db = Conexao::getInstancia();

        $stm = $db->prepare("DELETE FROM permissoes WHERE id = :id");
        $stm->execute(['id' => $id]);

        Helpers::log("Eliminou permissão ID $id", 'permissoes');

        $this->sessao->setFlash("Permissão eliminada com sucesso", "success");
        header("Location: " . Helpers::url('/permissoes'));
        exit;
    }
}