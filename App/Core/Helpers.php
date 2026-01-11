<?php

namespace App\Core;

class Helpers
{
    public static function url(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');

        if ($path === '' || $path === '/') {
            return $base ?: '/';
        }

        return $base . '/' . ltrim($path, '/');
    }

    public static function redirect(string $path): void
    {
        $url = self::url($path);
        header('Location: ' . $url, true, 302);
        exit;
    }
}