<?php

namespace app\Core;

use app\Core\Conexao;
use PDO;

class Auth
{
    public static function user(): ?object
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $db = Conexao::getInstancia();

        $stmt = $db->prepare("SELECT * FROM utilizadores WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $_SESSION['user_id']]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function login(int $id): void
    {
        $_SESSION['user_id'] = $id;
        Acl::flush(); // recarregar permissões
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        Acl::flush();
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
}