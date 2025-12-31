<?php

namespace App\Core;

class Helpers
{
    /**
     * Gera uma URL absoluta baseada no APP_URL do .env
     */
    public static function url(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        $path = '/' . ltrim($path, '/');

        return $base . $path;
    }

    /**
     * Gera URL para assets
     */
    public static function asset(string $path): string
    {
        return self::url('assets/' . ltrim($path, '/'));
    }

    /**
     * Redirecionamento seguro
     */
    public static function redirecionar(string $path): void
    {
        header("Location: " . self::url($path));
        exit;
    }

    /**
     * Verifica permissão usando o ACL moderno
     */
    public static function can(string $permissao): bool
    {
        return Acl::can($permissao);
    }
}