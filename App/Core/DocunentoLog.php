<?php
namespace App\Core;

use PDO;

class DocumentoLog
{
    public function __construct(private PDO $db) {}

    public function add(array $data): bool
    {
        $sql = "INSERT INTO documentos_logs 
                (documento_id, acao, detalhes, utilizador_id, criado_em)
                VALUES (:documento_id, :acao, :detalhes, :utilizador_id, NOW())";

        $stm = $this->db->prepare($sql);
        return $stm->execute($data);
    }

    public function allByDocumento(int $id): array
    {
        $stm = $this->db->prepare("SELECT * FROM documentos_logs WHERE documento_id = :id ORDER BY id DESC");
        $stm->execute(['id' => $id]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }
}