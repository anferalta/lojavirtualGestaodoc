<?php

namespace App\Core;

use App\Core\Conexao;
use PDO;

class Auth
{
    public static function attempt(string $email, string $senha): bool
    {
        $db = Conexao::getInstancia();

        $sql = "SELECT * FROM utilizadores WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$user || !password_verify($senha, $user->senha)) {
            return false;
        }

        $_SESSION['user_id'] = $user->id;
        return true;
    }

    public static function user(): ?object
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        static $cachedUser = null;

        if ($cachedUser) {
            return $cachedUser;
        }

        $db = Conexao::getInstancia();
        $stmt = $db->prepare("SELECT * FROM utilizadores WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $_SESSION['user_id']]);

        return $cachedUser = $stmt->fetch(PDO::FETCH_OBJ);
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
    }
}