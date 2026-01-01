<?php

namespace App\Core;

use App\Core\Conexao;
use App\Core\Auth;
use PDO;

class Notification
{
    public static function sendTo(int $userId, string $titulo, string $mensagem, ?string $tipo = null): void
    {
        $db = Conexao::getInstancia();

        $sql = "INSERT INTO notificacoes (utilizador_id, titulo, mensagem, tipo)
                VALUES (:uid, :titulo, :mensagem, :tipo)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':uid'      => $userId,
            ':titulo'   => $titulo,
            ':mensagem' => $mensagem,
            ':tipo'     => $tipo,
        ]);
    }

    public static function sendToCurrent(string $titulo, string $mensagem, ?string $tipo = null): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }
        self::sendTo($user->id, $titulo, $mensagem, $tipo);
    }

    public static function unreadForCurrent(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $db = Conexao::getInstancia();

        $sql = "SELECT * FROM notificacoes
                WHERE utilizador_id = :uid AND lida = 0
                ORDER BY criado_em DESC
                LIMIT 20";

        $stmt = $db->prepare($sql);
        $stmt->execute([':uid' => $user->id]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function markAsRead(int $id): void
    {
        $db = Conexao::getInstancia();

        $sql = "UPDATE notificacoes
                SET lida = 1, lida_em = NOW()
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}