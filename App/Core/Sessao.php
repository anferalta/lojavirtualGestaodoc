<?php

namespace App\Core;

class Sessao
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
     * Obter flash message (e remover)
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
     * CSRF
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
}