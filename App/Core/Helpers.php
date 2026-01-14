<?php

namespace App\Core;

class Helpers
{
    /**
     * Gera URL absoluta baseada no APP_URL
     */
    public static function url(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');

        if ($path === '' || $path === '/') {
            return $base ?: '/';
        }

        return $base . '/' . ltrim($path, '/');
    }

    /**
     * Gera URL para assets (CSS, JS, imagens)
     */
    public static function asset(string $path): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return $base . '/assets/' . ltrim($path, '/');
    }

    /**
     * Redirecionamento simples
     */
    public static function redirect(string $path): void
    {
        header('Location: ' . self::url($path), true, 302);
        exit;
    }

    /**
     * Campo CSRF automático para formulários
     */
    public static function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . \App\Core\Sessao::csrf() . '">';
    }

    /**
     * Guardar mensagem flash
     */
    public static function flash(string $key, string $message): void
    {
        Sessao::start();
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Obter e remover mensagem flash
     */
    public static function get_flash(string $key): ?string
    {
        Sessao::start();
        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    /**
     * Repovoar formulários após erro
     */
    public static function old(string $key, string $default = ''): string
    {
        Sessao::start();
        return $_SESSION['old'][$key] ?? $default;
    }

    /**
     * Guardar valores antigos do formulário
     */
    public static function save_old(array $data): void
    {
        Sessao::start();
        $_SESSION['old'] = $data;
    }

    /**
     * Limpar valores antigos
     */
    public static function clear_old(): void
    {
        Sessao::start();
        unset($_SESSION['old']);
    }

    /**
     * Verificar se uma rota está ativa (para menus)
     */
    public static function is_active(string $path): string
    {
        $current = $_SERVER['REQUEST_URI'] ?? '';
        return str_starts_with($current, $path) ? 'active' : '';
    }

    /**
     * Gerar URL baseada no router (rotas nomeadas)
     */
    public static function route(string $name, array $params = []): string
    {
        return Router::generate($name, $params);
    }

    /**
     * Debug elegante
     */
    public static function dd(...$vars): void
    {
        echo "<pre style='background:#111;color:#0f0;padding:15px;border-radius:6px;font-size:14px'>";
        foreach ($vars as $v) {
            print_r($v);
            echo "\n\n";
        }
        echo "</pre>";
        exit;
    }
}