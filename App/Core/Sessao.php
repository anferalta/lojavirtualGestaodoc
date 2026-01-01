<?php

namespace App\Core;

class Sessao
{
    /**
     * Iniciar sessão de forma segura
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Definir valor na sessão
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obter valor da sessão
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar se existe chave na sessão
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remover chave da sessão
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Criar flash message
     */
    public static function flash(string $msg, string $tipo = 'info'): void
{
    $_SESSION['flash'] = [
        'msg'  => $msg,
        'tipo' => $tipo
    ];
}

    /**
     * Obter e limpar flash message
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
     * CSRF token seguro
     */
    public static function csrf(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    /**
     * Validar CSRF
     */
    public static function validarCsrf(string $token): bool
    {
        return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    }

    /**
     * Limpar sessão (sem destruir cookie)
     */
    public static function clear(): void
    {
        $_SESSION = [];
    }

    /**
     * Logout seguro
     */
    public static function logout(): void
    {
        self::clear();

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