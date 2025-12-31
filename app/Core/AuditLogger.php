<?php

namespace app\Core;

use app\Core\Conexao;
use app\Core\Auth;
use PDO;

class AuditLogger
{
    public static function log(string $acao, ?string $detalhe = null): void
    {
        $db = Conexao::getInstancia();

        $sql = "INSERT INTO auditoria (utilizador_id, acao, detalhe, ip, user_agent)
                VALUES (:uid, :acao, :detalhe, :ip, :ua)";

        $stmt = $db->prepare($sql);

        $user = Auth::user();

        $stmt->execute([
            ':uid'     => $user->id ?? null,
            ':acao'    => $acao,
            ':detalhe' => $detalhe,
            ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua'      => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}