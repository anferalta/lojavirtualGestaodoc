<?php

namespace App\Core;

use App\Core\Conexao;
use App\Core\Sessao;
use App\Core\AuditLogger;
use PDO;
use PDOException;

class Auth
{
    public static function user(): ?object
    {
        $id = Sessao::get('user_id');

        if (!$id) {
            return null;
        }

        try {
            $db = Conexao::getInstancia();

            $stmt = $db->prepare("
                SELECT *
                FROM utilizadores
                WHERE id = :id AND estado != -1
                LIMIT 1
            ");

            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ) ?: null;

        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return null;
        }
    }

    public static function check(): bool
    {
        return Sessao::has('user_id');
    }

    public static function login(int $id): void
    {
        Sessao::set('user_id', $id);

        try {
            $db = Conexao::getInstancia();
            $stmt = $db->prepare("UPDATE utilizadores SET ultimo_login = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
        }

        Acl::flush();
        AuditLogger::log('login', "Utilizador ID: $id");
    }

    public static function logout(): void
    {
        $id = Sessao::get('user_id');

        Sessao::logout();
        Acl::flush();

        AuditLogger::log('logout', "Utilizador ID: $id");
    }

    public static function id(): ?int
    {
        return Sessao::get('user_id');
    }

    public static function attempt(string $email, string $senha): bool
    {
        try {
            $db = Conexao::getInstancia();

            $stmt = $db->prepare("
                SELECT *
                FROM utilizadores
                WHERE email = :email AND estado != -1
                LIMIT 1
            ");

            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$user) {
                AuditLogger::log('login_falhado', "Email inexistente: $email");
                return false;
            }

            if (!password_verify($senha, $user->senha)) {
                AuditLogger::log('login_falhado', "Password incorreta para: $email");
                return false;
            }

            self::login($user->id);
            return true;

        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return false;
        }
    }
}