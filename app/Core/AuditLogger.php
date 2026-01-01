<?php

namespace App\Core;

use App\Core\Conexao;
use App\Core\Auth;
use PDO;

class AuditLogger
{
    public static function log(string $acao, ?string $detalhe = null): void
    {
        $db = Conexao::getInstancia();

        $sql = "INSERT INTO auditoria (utilizador_id, acao, detalhe, ip, user_agent, criado_em)
                VALUES (:uid, :acao, :detalhe, :ip, :ua, :criado)";

        $stmt = $db->prepare($sql);

        $user = Auth::user();

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? null;

        $stmt->execute([
            ':uid'     => $user->id ?? null,
            ':acao'    => $acao,
            ':detalhe' => $detalhe,
            ':ip'      => $ip,
            ':ua'      => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':criado'  => date('Y-m-d H:i:s'),
        ]);
    }
}