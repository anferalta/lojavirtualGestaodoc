<?php

namespace App\Core;

class Helpers {
    /*
      |--------------------------------------------------------------------------
      | URL Helpers
      |--------------------------------------------------------------------------
     */

    /**
     * Gera uma URL absoluta baseada no domínio atual.
     */
    public static function url(string $path = ''): string {
        $path = '/' . ltrim($path, '/');
        return $path;
    }

    /**
     * Gera o caminho para assets dentro de /public/assets/
     */
    public static function asset(string $path): string {
        $path = ltrim($path, '/');
        return "/assets/{$path}";
    }

    /*
      |--------------------------------------------------------------------------
      | ACL Helpers
      |--------------------------------------------------------------------------
     */

    /**
     * Verifica permissões usando o ACL central.
     */
    public static function can(string $permission): bool {
        return ACL::can($permission);
    }

    /*
      |--------------------------------------------------------------------------
      | Sanitização e Segurança
      |--------------------------------------------------------------------------
     */

    /**
     * Escapa HTML (útil fora do Twig).
     */
    public static function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiza strings simples.
     */
    public static function clean(string $value): string {
        return trim(strip_tags($value));
    }

    /*
      |--------------------------------------------------------------------------
      | Redirecionamento
      |--------------------------------------------------------------------------
     */

    public static function redirect(string $url): void {
        header("Location: " . self::url($url));
        exit;
    }

    public static function redirecionar(string $url): void {
        header("Location: $url");
        exit;
    }

    /*
      |--------------------------------------------------------------------------
      | Debug
      |--------------------------------------------------------------------------
     */

    public static function dd(...$vars): void {
        echo "<pre>";
        foreach ($vars as $v) {
            var_dump($v);
        }
        echo "</pre>";
        exit;
    }
}
