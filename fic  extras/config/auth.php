<?php
namespace app\Core;

class Auth
{
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function verificarLogin(): void
    {
        self::startSession();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['erro_login'] = "É necessário estar logado para acessar esta página.";
            header("Location: " . Helpers::url('/login'));
            exit;
        }
    }

    public static function verificarNivel(int $nivelNecessario): void
    {
        self::startSession();
        if (!isset($_SESSION['usuario_level']) || $_SESSION['usuario_level'] < $nivelNecessario) {
            $_SESSION['erro_permissao'] = "Acesso negado. Permissão insuficiente.";
            header("Location: " . Helpers::url('/painel'));
            exit;
        }
    }

    public static function login(int $id, int $level): void
    {
        self::startSession();
        $_SESSION['user_id'] = $id;
        $_SESSION['usuario_level'] = $level;
    }

    public static function logout(): void
    {
        self::startSession();
        session_destroy();
        header("Location: " . Helpers::url('/login'));
        exit;
    }
}