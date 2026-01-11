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

    /* -----------------------------
     *  MÉTODOS ESSENCIAIS DE SESSÃO
     * ----------------------------- */

    public static function set(string $chave, mixed $valor): void
    {
        $_SESSION[$chave] = $valor;
    }

    public static function get(string $chave, mixed $default = null): mixed
    {
        return $_SESSION[$chave] ?? $default;
    }

    public static function existe(string $chave): bool
    {
        return isset($_SESSION[$chave]);
    }

    // Alias para compatibilidade com middleware
    public static function tem(string $chave): bool
    {
        return self::existe($chave);
    }

    public static function remover(string $chave): void
    {
        unset($_SESSION[$chave]);
    }

    public static function limpar(): void
    {
        session_destroy();
    }

    /* -----------------------------
     *  FLASH MESSAGES
     * ----------------------------- */

    public static function flash(string $tipo, string $mensagem): void
    {
        if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }

        $_SESSION['flash'][] = [
            'tipo' => $tipo,
            'mensagem' => $mensagem,
        ];
    }

    public static function getFlash(): array
    {
        if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
            return [];
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    /* -----------------------------
     *  CSRF
     * ----------------------------- */

    public static function csrf(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    public static function regenerateCsrf(): void
    {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    public static function validarCsrf(string $token): bool
    {
        return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    }
}