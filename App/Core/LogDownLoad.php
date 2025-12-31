<?php

namespace app\Core;

use PDO;

class LogDownload
{
    private PDO $db;
    private string $table = 'logs_downloads';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function add(int $docId, int $userId): void
    {
        $sql = "INSERT INTO {$this->table} (documento_id, user_id, ip, user_agent)
                VALUES (:doc, :user, :ip, :ua)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':doc'  => $docId,
            ':user' => $userId,
            ':ip'   => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua'   => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}