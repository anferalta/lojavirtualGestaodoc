<?php

namespace app\Core;

class Env
{
    public static function load(string $path = null, bool $overrideExisting = false): void
    {
        $file = $path ?? __DIR__ . '/../../.env';

        if (!file_exists($file)) {
            // Em produção pode ser silencioso.
            // Em dev, se quiseres, podes fazer um log aqui.
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignorar comentários e linhas vazias
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Remover comentários inline
            if (str_contains($line, '#')) {
                $line = substr($line, 0, strpos($line, '#'));
                $line = trim($line);
            }

            // Garantir que existe "="
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            // Respeitar variáveis já definidas, se $overrideExisting = false
            if (!$overrideExisting && (array_key_exists($key, $_ENV) || getenv($key) !== false)) {
                continue;
            }

            // Remover aspas se existirem
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}