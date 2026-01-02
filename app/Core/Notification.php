<?php

namespace App\Core;

use App\Core\Conexao;
use App\Core\Auth;
use App\Core\AuditLogger;
use PDO;
use PDOException;

class Notification
{
    /**
     * Enviar notificação para um utilizador específico
     */
    public static function sendTo(int $userId, string $titulo, string $mensagem, ?string $tipo = null): bool
    {
        try {
            $db = Conexao::getInstancia();

            $sql = "INSERT INTO notificacoes (utilizador_id, titulo, mensagem, tipo)
                    VALUES (:uid, :titulo, :mensagem, :tipo)";

            $stmt = $db->prepare($sql);

            return $stmt->execute([
                ':uid'      => $userId,
                ':titulo'   => $titulo,
                ':mensagem' => $mensagem,
                ':tipo'     => $tipo,
            ]);

        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificação para o utilizador autenticado
     */
    public static function sendToCurrent(string $titulo, string $mensagem, ?string $tipo = null): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return self::sendTo($user->id, $titulo, $mensagem, $tipo);
    }

    /**
     * Obter notificações não lidas do utilizador atual
     */
    public static function unreadForCurrent(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        try {
            $db = Conexao::getInstancia();

            $sql = "SELECT * FROM notificacoes
                    WHERE utilizador_id = :uid AND lida = 0
                    ORDER BY criado_em DESC
                    LIMIT 20";

            $stmt = $db->prepare($sql);
            $stmt->execute([':uid' => $user->id]);

            return $stmt->fetchAll(PDO::FETCH_OBJ);

        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return [];
        }
    }

    /**
     * Obter todas as notificações do utilizador atual
     */
    public static function allForCurrent(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        try {
            $db = Conexao::getInstancia();

            $sql = "SELECT * FROM notificacoes
                    WHERE utilizador_id = :uid
                    ORDER BY criado_em DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute([':uid' => $user->id]);

            return $stmt->fetchAll(PDO::FETCH_OBJ);

        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return [];
        }
    }

    /**
     * Marcar uma notificação como lida
     */
    public static function markAsRead(int $id): bool
    {
        try {
            $db = Conexao::getInstancia();

            $sql = "UPDATE notificacoes
                    SET lida = 1, lida_em = NOW()
                    WHERE id = :id";

            $stmt = $db->prepare($sql);
            return $stmt->execute([':id' => $id]);

        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public static function markAllAsRead(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        try {
            $db = Conexao::getInstancia();

            $sql = "UPDATE notificacoes
                    SET lida = 1, lida_em = NOW()
                    WHERE utilizador_id = :uid AND lida = 0";

            $stmt = $db->prepare($sql);
            return $stmt->execute([':uid' => $user->id]);

        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return false;
        }
    }

    /**
     * Apagar uma notificação
     */
    public static function delete(int $id): bool
    {
        try {
            $db = Conexao::getInstancia();

            $sql = "DELETE FROM notificacoes WHERE id = :id";

            $stmt = $db->prepare($sql);
            return $stmt->execute([':id' => $id]);

        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return false;
        }
    }
}