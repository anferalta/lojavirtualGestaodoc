<?php
namespace App\Model;

use App\Core\Conexao;
use PDO;

class LoginModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexao::getInstancia();
    }

    /**
     * Autentica um utilizador e devolve o objeto completo
     * ou null se falhar.
     */
    public function autenticar(string $email, string $senha): ?object
    {
        $sql = "SELECT id, nome, email, senha, level
                FROM utilizadores
                WHERE email = :email AND estado = 1
                LIMIT 1";

        $stm = $this->db->prepare($sql);
        $stm->execute(['email' => $email]);
        $user = $stm->fetch(PDO::FETCH_OBJ);

        if (!$user) {
            return null;
        }

        if (!password_verify($senha, $user->senha)) {
            return null;
        }

        unset($user->senha);

        return $user;
    }
}