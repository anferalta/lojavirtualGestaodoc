<?php

namespace app\Core;

class Sessao
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Flash message (mensagem temporária)
     */
    public static function flash(?string $msg, string $tipo = 'info'): void
    {
        $_SESSION['flash'] = [
            'msg'  => $msg ?? '',
            'tipo' => $tipo
        ];
    }

    /**
     * Obter e limpar flash
     */
    public static function getFlash(): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    /**
     * CSRF token
     */
    public static function csrf(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function validarCsrf(string $token): bool
    {
        return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    }

    /**
     * Login
     */
    public static function login(object $user): void
    {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['usuario_nome'] = $user->nome;
        $_SESSION['ultimo_login'] = date('Y-m-d H:i:s');
    }

    /**
     * Logout seguro
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }
}