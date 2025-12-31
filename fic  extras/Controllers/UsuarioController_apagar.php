
<?php
namespace app\Controllers;

use app\Core\BaseController;
use app\Core\Conexao;
use app\Core\Sessao;
use app\Core\Helpers;

class UsuarioController extends BaseController
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
        $pagina = max(1, intval($_GET['p'] ?? 1));
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT * FROM utilizadores WHERE nome LIKE :pesquisa OR email LIKE :pesquisa LIMIT :offset, :limit";
        $stm = $db->prepare($sql);
        $stm->bindValue(':pesquisa', "%$pesquisa%");
        $stm->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stm->bindValue(':limit', $porPagina, \PDO::PARAM_INT);
        $stm->execute();

        $utilizadores = $stm->fetchAll();

        // Total para paginação
        $sqlTotal = "SELECT COUNT(*) FROM utilizadores WHERE nome LIKE :pesquisa OR email LIKE :pesquisa";
        $stmTotal = $db->prepare($sqlTotal);
        $stmTotal->execute([':pesquisa' => "%$pesquisa%"]);
        $total = $stmTotal->fetchColumn();

        echo $this->twig->render('utilizadores/index.twig', [
            'titulo' => 'Gestão de Utilizadores',
            'utilizadores' => $utilizadores,
            'pesquisa' => $pesquisa,
            'pagina' => $pagina,
            'total' => $total,
            'porPagina' => $porPagina
        ]);
    }

    public function editar(int $id): void
    {
        $db = Conexao::getInstancia();

        $sql = "SELECT * FROM utilizadores WHERE id = :id LIMIT 1";
        $stm = $db->prepare($sql);
        $stm->execute(['id' => $id]);
        $user = $stm->fetch();

        if (!$user) {
            $this->sessao->setFlash("Utilizador não encontrado", "danger");
            header("Location: /utilizadores");
            exit;
        }

        echo $this->twig->render('utilizadores/editar.twig', [
            'titulo' => 'Editar Utilizador',
            'user' => $user
        ]);
    }

    public function atualizar(int $id): void
    {
        $db = Conexao::getInstancia();

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $estado = isset($_POST['estado']) ? 1 : 0;

        if ($nome === '' || $email === '') {
            $this->sessao->setFlash("Preencha todos os campos obrigatórios", "danger");
            header("Location: /utilizadores/editar/$id");
            exit;
        }

        $sql = "UPDATE utilizadores SET nome = :nome, email = :email, estado = :estado WHERE id = :id";
        $stm = $db->prepare($sql);
        $stm->execute([
            'nome' => $nome,
            'email' => $email,
            'estado' => $estado,
            'id' => $id
        ]);

        Helpers::log("Atualizou utilizador ID $id");

        $this->sessao->setFlash("Utilizador atualizado com sucesso", "success");
        header("Location: /utilizadores");
        exit;
    }
}
