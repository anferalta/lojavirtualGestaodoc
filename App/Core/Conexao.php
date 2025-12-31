<?php

namespace app\Core;

use PDO;
use PDOException;

class Conexao
{
    private static ?PDO $instancia = null;

    public static function getInstancia(): PDO
    {
        if (self::$instancia !== null) {
            return self::$instancia;
        }

        // Garantir que o .env foi carregado
        if (empty($_ENV['DB_HOST'])) {
            throw new \RuntimeException("As variáveis de ambiente não foram carregadas. Falta chamar Env::load().");
        }

        // Ler variáveis do .env
        $host    = $_ENV['DB_HOST'];
        $db      = $_ENV['DB_NAME'];
        $user    = $_ENV['DB_USER'];
        $pass    = $_ENV['DB_PASS'];
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        // Validar variáveis obrigatórias
        foreach (['DB_HOST','DB_NAME','DB_USER'] as $var) {
            if (empty($_ENV[$var])) {
                throw new \RuntimeException("Variável de ambiente obrigatória em falta: {$var}");
            }
        }

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

        try {
            self::$instancia = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException("Erro ao conectar à base de dados: " . $e->getMessage());
        }

        return self::$instancia;
    }
}