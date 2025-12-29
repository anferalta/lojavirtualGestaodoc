<?php
namespace App\Core;

use PDO;

class Documento
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        return $this->db->query("SELECT * FROM documentos ORDER BY id DESC")
                        ->fetchAll(PDO::FETCH_OBJ);
    }

    public function countAll(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM documentos")->fetchColumn();
    }

    public function getPage(int $limit, int $offset): array
    {
        $sql = "SELECT * FROM documentos ORDER BY id DESC LIMIT :lim OFFSET :off";
        $stm = $this->db->prepare($sql);
        $stm->bindValue('lim', $limit, PDO::PARAM_INT);
        $stm->bindValue('off', $offset, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    public function find(int $id): ?object
    {
        $stm = $this->db->prepare("SELECT * FROM documentos WHERE id = :id");
        $stm->execute(['id' => $id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO documentos 
                (titulo, descricao, ficheiro, tipo, tamanho, estado, criado_por, criado_em)
                VALUES (:titulo, :descricao, :ficheiro, :tipo, :tamanho, :estado, :criado_por, NOW())";

        $stm = $this->db->prepare($sql);
        return $stm->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE documentos SET 
                titulo = :titulo,
                descricao = :descricao,
                estado = :estado,
                atualizado_em = NOW()
                WHERE id = :id";

        $stm = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stm->execute($data);
    }

    public function delete(int $id): bool
    {
        $stm = $this->db->prepare("DELETE FROM documentos WHERE id = :id");
        return $stm->execute(['id' => $id]);
    }
}