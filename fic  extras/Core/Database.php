<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $conexao = null;

    // Impede clonagem
    private function __clone(): void {}

    public static function getConexao(): PDO
    {
        if (self::$conexao === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $banco = $_ENV['DB_NAME'] ?? '';
                $usuario = $_ENV['DB_USER'] ?? '';
                $senha = $_ENV['DB_PASS'] ?? '';
                $porta = $_ENV['DB_PORT'] ?? 3306;
                $charset = 'utf8mb4';

                $dsn = "mysql:host={$host};port={$porta};dbname={$banco};charset={$charset}";

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_PERSISTENT         => false // podes ativar se quiseres
                ];

                self::$conexao = new PDO($dsn, $usuario, $senha, $options);

            } catch (PDOException $ex) {
                // Log interno (não mostrar detalhes ao utilizador)
                error_log("Erro BD: " . $ex->getMessage());

                // Mensagem genérica
                throw new PDOException("Falha ao conectar à base de dados.");
            }
        }

        return self::$conexao;
    }
}