<?php
namespace App\Middleware;

use App\Core\Conexao;

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
        // Obter headers normalizados
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);

        // Extrair token
        $token = $headers['authorization'] ?? '';

        if (str_starts_with($token, 'bearer ')) {
            $token = substr($token, 7);
        }

        if (!$token) {
            self::error('Token em falta');
        }

        // Validar token na BD
        $db = Conexao::getInstancia();
        $stm = $db->prepare("SELECT * FROM api_tokens WHERE token = :token LIMIT 1");
        $stm->execute(['token' => $token]);
        $row = $stm->fetch();

        if (!$row) {
            self::error('Token inválido');
        }

        // Opcional: colocar user/token no contexto global
        // $_SESSION['api_user_id'] = $row['user_id'];
    }
}