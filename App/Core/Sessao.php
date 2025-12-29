<?php
namespace App\Core;

class Sessao {

    /**
     * Inicia a sessão se ainda não estiver ativa
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Define um valor na sessão
     */
    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtém um valor da sessão
     */
    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica se existe uma chave na sessão
     */
    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove uma chave da sessão
     */
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Limpa toda a sessão
     */
    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Flash message (ler e apagar)
     */
    public static function flash(): ?array {
        self::start();

        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $msg;
    }

    /**
     * Define uma flash message
     */
    public static function setFlash(string $mensagem, string $tipo = 'info'): void {
        self::start();
        $_SESSION['flash'] = [
            'mensagem' => $mensagem,
            'tipo' => $tipo
        ];
    }

    /**
     * Token CSRF consistente com o middleware
     */
    public static function csrf(): string {
        self::start();

        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    /**
     * Alias para has() — compatibilidade com middlewares antigos
     */
    public static function tem(string $key): bool {
        return self::has($key);
    }
}
