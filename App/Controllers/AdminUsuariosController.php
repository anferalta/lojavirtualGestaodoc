
<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use App\Core\Sessao;
use App\Core\Helpers;

class AdminUsuariosController extends BaseController
{
    private Sessao $sessao;

    public function __construct()
    {
        parent::__construct();
        $this->sessao = new Sessao();
    }

    public function index(): void
    {
        $db = Conexao::getInstancia();

        $pesquisa = $_GET['q'] ?? '';
        $perfil = $_GET['perfil'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $pagina = max(1, intval($_GET['p'] ?? 1));
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT u.*, p.nome AS perfil_nome
                FROM utilizadores u
                LEFT JOIN perfis p ON p.id = u.perfil_id
                WHERE (u.nome LIKE :pesquisa OR u.email LIKE :pesquisa)";

        if ($perfil !== '') {
            $sql .= " AND u.perfil_id = :perfil";
        }

        if ($estado !== '') {
            $sql .= " AND u.estado = :estado";
        }

        $sql .= " LIMIT :offset, :limit";

        $stm = $db->prepare($sql);
        $stm->bindValue(':pesquisa', "%$pesquisa%");
        if ($perfil !== '') $stm->bindValue(':perfil', $perfil);
        if ($estado !== '') $stm->bindValue(':estado', $estado);
        $stm->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stm->bindValue(':limit', $porPagina, \PDO::PARAM_INT);
        $stm->execute();

        $utilizadores = $stm->fetchAll();

        // Total para paginação
        $sqlTotal = "SELECT COUNT(*) FROM utilizadores WHERE nome LIKE :pesquisa OR email LIKE :pesquisa";
        $stmTotal = $db->prepare($sqlTotal);
        $stmTotal->execute([':pesquisa' => "%$pesquisa%"]);
        $total = $stmTotal->fetchColumn();

        // Perfis para filtro
        $perfis = $db->query("SELECT * FROM perfis ORDER BY nome")->fetchAll();

        echo $this->twig->render('admin_usuarios/index.twig', [
            'titulo' => 'Administração de Utilizadores',
            'utilizadores' => $utilizadores,
            'pesquisa' => $pesquisa,
            'perfil' => $perfil,
            'estado' => $estado,
            'pagina' => $pagina,
            'total' => $total,
            'porPagina' => $porPagina,
            'perfis' => $perfis
        ]);
    }

    public function criar(): void
    {
        $db = Conexao::getInstancia();
        $perfis = $db->query("SELECT * FROM perfis ORDER BY nome")->fetchAll();

        echo $this->twig->render('admin_usuarios/criar.twig', [
            'titulo' => 'Criar Utilizador',
            'perfis' => $perfis
        ]);
    }

    public function guardar(): void
    {
        $db = Conexao::getInstancia();

        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);
        $perfil_id = $_POST['perfil_id'] ?? null;

        if ($nome === '' || $email === '' || $senha === '') {
            $this->sessao->setFlash("Preencha todos os campos obrigatórios", "danger");
            header("Location: /admin/utilizadores/criar");
            exit;
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO utilizadores (nome, email, senha, perfil_id, estado) 
                VALUES (:nome, :email, :senha, :perfil_id, 1)";
        $stm = $db->prepare($sql);
        $stm->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $hash,
            'perfil_id' => $perfil_id
        ]);

        Helpers::log("Criou utilizador $email");

        $this->sessao->setFlash("Utilizador criado com sucesso", "success");
        header("Location: /admin/utilizadores");
        exit;
    }

    public function editar(int $id): void
    {
        $db = Conexao::getInstancia();

        $stm = $db->prepare("SELECT * FROM utilizadores WHERE id = :id");
        $stm->execute(['id' => $id]);
        $user = $stm->fetch();

        if (!$user) {
            $this->sessao->setFlash("Utilizador não encontrado", "danger");
            header("Location: /admin/utilizadores");
            exit;
        }

        $perfis = $db->query("SELECT * FROM perfis ORDER BY nome")->fetchAll();

        echo $this->twig->render('admin_usuarios/editar.twig', [
            'titulo' => 'Editar Utilizador',
            'user' => $user,
            'perfis' => $perfis
        ]);
    }

    public function atualizar(int $id): void
    {
        $db = Conexao::getInstancia();

        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $perfil_id = $_POST['perfil_id'] ?? null;
        $estado = isset($_POST['estado']) ? 1 : 0;

        if ($nome === '' || $email === '') {
            $this->sessao->setFlash("Preencha todos os campos obrigatórios", "danger");
            header("Location: /admin/utilizadores/editar/$id");
            exit;
        }

        $sql = "UPDATE utilizadores SET nome = :nome, email = :email, perfil_id = :perfil_id, estado = :estado WHERE id = :id";
        $stm = $db->prepare($sql);
        $stm->execute([
            'nome' => $nome,
            'email' => $email,
            'perfil_id' => $perfil_id,
            'estado' => $estado,
            'id' => $id
        ]);

        Helpers::log("Atualizou utilizador ID $id");

        $this->sessao->setFlash("Utilizador atualizado com sucesso", "success");
        header("Location: /admin/utilizadores");
        exit;
    }

    public function permissoes(int $id): void
    {
        $db = Conexao::getInstancia();

        $stm = $db->prepare("SELECT * FROM utilizadores WHERE id = :id");
        $stm->execute(['id' => $id]);
        $user = $stm->fetch();

        if (!$user) {
            $this->sessao->setFlash("Utilizador não encontrado", "danger");
            header("Location: /admin/utilizadores");
            exit;
        }

        $permissoes = $db->query("SELECT * FROM permissoes ORDER BY codigo")->fetchAll();

        $stm2 = $db->prepare("SELECT permissao_id FROM utilizador_permissoes WHERE utilizador_id = :id");
        $stm2->execute(['id' => $id]);
        $permissoesUser = array_column($stm2->fetchAll(), 'permissao_id');

        echo $this->twig->render('admin_usuarios/permissoes.twig', [
            'titulo' => 'Permissões do Utilizador',
            'user' => $user,
            'permissoes' => $permissoes,
            'permissoesUser' => $permissoesUser
        ]);
    }

    public function guardarPermissoes(int $id): void
    {
        $db = Conexao::getInstancia();

        $db->prepare("DELETE FROM utilizador_permissoes WHERE utilizador_id = :id")
           ->execute(['id' => $id]);

        if (!empty($_POST['permissoes'])) {
            foreach ($_POST['permissoes'] as $perm) {
                $stm = $db->prepare("INSERT INTO utilizador_permissoes (utilizador_id, permissao_id) VALUES (:u, :p)");
                $stm->execute(['u' => $id, 'p' => $perm]);
            }
        }

        Helpers::log("Atualizou permissões do utilizador ID $id");

        $this->sessao->setFlash("Permissões atualizadas com sucesso", "success");
        header("Location: /admin/utilizadores/permissoes/$id");
        exit;
    }

    public function logs(int $id): void
    {
        $db = Conexao::getInstancia();

        $stm = $db->prepare("SELECT * FROM utilizadores WHERE id = :id");
        $stm->execute(['id' => $id]);
        $user = $stm->fetch();

        if (!$user) {
            $this->sessao->setFlash("Utilizador não encontrado", "danger");
            header("Location: /admin/utilizadores");
            exit;
        }

        $stm2 = $db->prepare("SELECT * FROM logs WHERE utilizador_id = :id ORDER BY criado_em DESC");
        $stm2->execute(['id' => $id]);
        $logs = $stm2->fetchAll();

        echo $this->twig->render('admin_usuarios/logs.twig', [
            'titulo' => 'Logs do Utilizador',
            'user' => $user,
            'logs' => $logs
        ]);
    }

    public function eliminar(int $id): void
    {
        if ($id == 1) {
            $this->sessao->setFlash("Não é possível eliminar o utilizador principal", "danger");
            header("Location: /admin/utilizadores");
            exit;
        }

        $db = Conexao::getInstancia();

        $stm = $db->prepare("DELETE FROM utilizadores WHERE id = :id");
        $stm->execute(['id' => $id]);

        Helpers::log("Eliminou utilizador ID $id");

        $this->sessao->setFlash("Utilizador eliminado com sucesso", "success");
        header("Location: /admin/utilizadores");
        exit;
    }
}
