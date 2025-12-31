<?php

namespace app\Core;

use PDO;

class Documento
{
    private PDO $db;
    private string $table = 'documentos';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function map(array $data): object
    {
        return (object) [
            'id'           => (int) $data['id'],
            'titulo'       => $data['titulo'],
            'descricao'    => $data['descricao'] ?? null,
            'caminho'      => $data['caminho'],
            'owner_id'     => (int) $data['owner_id'],
            'estado'       => $data['estado'],
            'criado_em'    => $data['criado_em'] ?? null,
            'atualizado_em'=> $data['atualizado_em'] ?? null,

            'isAtivo'      => fn() => $data['estado'] === 'ativo',
        ];
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->db->query($sql);

        return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function paginate(int $limit, int $offset): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    public function find(int $id): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->map($data) : null;
    }

   public function create(array $dados): bool
{
    $sql = "INSERT INTO {$this->table} (titulo, descricao, caminho, owner_id, estado)
            VALUES (:titulo, :descricao, :caminho, :owner_id, :estado)";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        ':titulo'    => $dados['titulo'],
        ':descricao' => $dados['descricao'] ?? null,
        ':caminho'   => $dados['caminho'],
        ':owner_id'  => $dados['owner_id'],
        ':estado'    => $dados['estado'] ?? 'ativo',
    ]);
}

    public function update(int $id, array $dados): bool
    {
        $campos = [];
        $params = [':id' => $id];

        foreach ($dados as $campo => $valor) {
            $campos[] = "$campo = :$campo";
            $params[":$campo"] = $valor;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }
}