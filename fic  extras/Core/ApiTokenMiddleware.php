<?php
namespace App\Middleware;

use app\Core\Conexao;

class ApiTokenMiddleware
{
    private static function error(string $msg): void
    {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => $msg]);
        exit;
    }

    public static function handle(): void
    {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        $token = $headers['authorization'] ?? '';

        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        if (!$token) {
            self::error('Token em falta');
        }

        $db = Conexao::getInstancia();
        $stm = $db->prepare("SELECT * FROM api_tokens WHERE token = :token");
        $stm->execute(['token' => $token]);
        $row = $stm->fetch();

        if (!$row) {
            self::error('Token inválido');
        }

        // Aqui poderias colocar user em contexto global se quiseres
    }
}