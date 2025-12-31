<?php

namespace App\Core;

use App\Core\Utilizador;
use App\Core\Conexao;

class Auth
{
    private static ?Utilizador $userModel = null;

    private static function model(): Utilizador
    {
        if (!self::$userModel) {
            self::$userModel = new Utilizador(Conexao::getInstancia());
        }
        return self::$userModel;
    }

    public static function attempt(string $email, string $senha): bool
    {
        $user = self::model()->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($senha, $user->senha)) {
            return false;
        }

        if (!$user->isAtivo()) {
            return false;
        }

        $_SESSION['user_id']    = $user->id;
        $_SESSION['perfil_id']  = $user->perfil_id ?? null;
        $_SESSION['nivel']      = $user->nivel ?? null;

        self::model()->updateLastLogin($user->id);

        return true;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function user(): ?object
    {
        $id = self::id();
        if (!$id) {
            return null;
        }

        return self::model()->find($id);
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['perfil_id'], $_SESSION['nivel']);
        session_regenerate_id(true);
    }
}