<?php
namespace App\Core;

class Auditoria
{
    public static function log(int $userId, string $acao, ?int $docId = null): void
    {
        $db = Conexao::getInstancia();

        $sql = "INSERT INTO auditoria (user_id, acao, documento_id, ip)
                VALUES (:uid, :acao, :doc, :ip)";

        $stm = $db->prepare($sql);
        $stm->execute([
            'uid' => $userId,
            'acao' => $acao,
            'doc' => $docId,
            'ip'  => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}