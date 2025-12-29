<?php
namespace App\Core;

use PDO;
use PDOException;

class Conexao
{
    private static ?PDO $instancia = null;

    public static function getInstancia(): PDO
    {
        if (self::$instancia === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $db   = $_ENV['DB_NAME'] ?? '';
                $user = $_ENV['DB_USER'] ?? '';
                $pass = $_ENV['DB_PASS'] ?? '';
                $port = $_ENV['DB_PORT'] ?? 3306;

                $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

                self::$instancia = new PDO(
                    $dsn,
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                        PDO::ATTR_EMULATE_PREPARES   => false,  // prepared statements reais
                        PDO::ATTR_PERSISTENT         => false   // podes ativar se quiseres
                    ]
                );

            } catch (PDOException $e) {
                error_log("Erro na conexão: " . $e->getMessage());
                throw new \Exception("Falha ao conectar à base de dados.");
            }
        }

        return self::$instancia;
    }
}