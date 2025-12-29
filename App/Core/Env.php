<?php

namespace App\Core;

class Env
{
    public static function load(): void
    {
        
        $file = __DIR__ . '/../../.env';

        if (!file_exists($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {

            $line = trim($line);

            // Ignorar comentários e linhas inválidas
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

            // Remover aspas se existirem
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Guardar em todas as variáveis possíveis
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}