<?php
namespace App\Core;

use PDO;

class DocumentoVersao
{
    public function __construct(private PDO $db) {}

    public function create(array $data): bool
    {
        $sql = "INSERT INTO documentos_versoes 
                (documento_id, ficheiro, tipo, tamanho, criado_por, criado_em)
                VALUES (:documento_id, :ficheiro, :tipo, :tamanho, :criado_por, NOW())";

        $stm = $this->db->prepare($sql);
        return $stm->execute($data);
    }

    public function allByDocumento(int $id): array
    {
        $sql = "SELECT * FROM documentos_versoes 
                WHERE documento_id = :id ORDER BY id DESC";

        $stm = $this->db->prepare($sql);
        $stm->execute(['id' => $id]);

        return $stm->fetchAll(PDO::FETCH_OBJ);
    }
}