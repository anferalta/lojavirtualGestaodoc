<?php

namespace App\Core;

use PDO;

class Permission
{
    private PDO $db;
    private string $table = 'permissoes';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function map(array $data): object
    {
        return (object) [
            'id'        => (int) $data['id'],
            'chave'     => $data['chave'],   // ex: 'utilizadores.ver'
            'nome'      => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
        ];
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY chave ASC";
        $stmt = $this->db->query($sql);

        return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findByKey(string $key): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE chave = :chave LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':chave' => $key]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->map($data) : null;
    }
}