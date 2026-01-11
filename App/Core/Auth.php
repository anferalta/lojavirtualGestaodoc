<?php

namespace App\Core;

use PDO;
use App\Models\Usuario;

class Auth {

    protected static function db(): PDO {
        return Database::getConexao();
    }

    /* -----------------------------
     * LOGIN / LOGOUT
     * ----------------------------- */

    public static function login(Usuario $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
    }

    public static function logout(): void {
        unset($_SESSION['user_id']);
        unset($_SESSION['2fa_validado']);
        session_regenerate_id(true);
    }

    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function userId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function user(): ?Usuario {
        $id = self::userId();
        if (!$id) {
            return null;
        }
        return Usuario::find($id);
    }

    /* -----------------------------
     * LOGIN COM EMAIL + PASSWORD
     * ----------------------------- */

    public static function attempt(string $email, string $password): bool {
        $sql = 'SELECT id, email, senha FROM utilizadores WHERE email = :email LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $user = $stmt->fetchObject(Usuario::class);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user->senha)) {
            return false;
        }

        self::login($user);
        return true;
    }

    /* -----------------------------
     * 2FA
     * ----------------------------- */

    public static function validar2fa(string $codigo): bool {
        if (!self::check()) {
            return false;
        }

        $sql = "SELECT two_factor_secret FROM utilizadores WHERE id = :id LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->bindValue(':id', self::userId());
        $stmt->execute();

        $user = $stmt->fetch();

        if (!$user || empty($user->two_factor_secret)) {
            return false;
        }

        $totp = new \OTPHP\TOTP($user->two_factor_secret);
        return $totp->verify($codigo);
    }

    public static function twoFactorValidado(): bool {
        return isset($_SESSION['2fa_validado']) && $_SESSION['2fa_validado'] === true;
    }

    public static function marcar2faComoValidado(): void {
        $_SESSION['2fa_validado'] = true;
    }

    public static function guardarSecret2FA(int $id, string $secret): void {
        $sql = "UPDATE utilizadores SET two_factor_secret = :s WHERE id = :id";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([':s' => $secret, ':id' => $id]);
    }

    public static function ativar2FA(int $id): void {
        $sql = "UPDATE utilizadores SET two_factor_ativo = 1 WHERE id = :id";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public static function desativar2FA(int $id): void {
        $sql = "UPDATE utilizadores SET two_factor_ativo = 0, two_factor_secret = NULL WHERE id = :id";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    /* -----------------------------
     * RECUPERAÇÃO DE PASSWORD
     * ----------------------------- */

    public static function gerarTokenRecuperacao(string $email): ?string {
        $sql = "SELECT id FROM utilizadores WHERE email = :email LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch();
        if (!$user)
            return null;

        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', time() + 3600);

        $sql = "UPDATE utilizadores SET reset_token = :t, reset_token_expira = :e WHERE id = :id";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([':t' => $token, ':e' => $expira, ':id' => $user->id]);

        return $token;
    }

    public static function validarToken(string $token): ?object {
        $sql = "SELECT * FROM utilizadores WHERE reset_token = :t AND reset_token_expira > NOW() LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([':t' => $token]);

        return $stmt->fetch() ?: null;
    }

    public static function redefinirPassword(int $id, string $nova): void {
        $hash = password_hash($nova, PASSWORD_DEFAULT);

        $sql = "UPDATE utilizadores 
            SET senha = :s, reset_token = NULL, reset_token_expira = NULL 
            WHERE id = :id";

        $stmt = self::db()->prepare($sql);
        $stmt->execute([':s' => $hash, ':id' => $id]);
    }
}