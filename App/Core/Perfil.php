<?php

namespace App\Core;

use PDO;

class Perfil
{
    private PDO $db;
    private string $table = 'perfis';
    private string $pivotTable = 'perfis_permissoes';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function map(array $data): object
    {
        return (object) [
            'id'        => (int) $data['id'],
            'nome'      => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'estado'    => $data['estado'] ?? 'ativo',

            'isAtivo'   => fn() => ($data['estado'] ?? 'ativo') === 'ativo',
        ];
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY nome ASC";
        $stmt = $this->db->query($sql);

        return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
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
        $sql = "INSERT INTO {$this->table} (nome, descricao, estado)
                VALUES (:nome, :descricao, :estado)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nome'      => $dados['nome'],
            ':descricao' => $dados['descricao'] ?? null,
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
        // Apaga associações de permissões primeiro
        $sqlPivot = "DELETE FROM {$this->pivotTable} WHERE perfil_id = :id";
        $stmtPivot = $this->db->prepare($sqlPivot);
        $stmtPivot->execute([':id' => $id]);

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Permissões associadas a um perfil (objetos Permission)
     */
    public function getPermissions(int $perfilId): array
    {
        $sql = "SELECT p.*
                FROM permissoes p
                INNER JOIN {$this->pivotTable} pp ON pp.permissao_id = p.id
                WHERE pp.perfil_id = :perfil_id
                ORDER BY p.chave ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':perfil_id' => $perfilId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => (object) [
            'id'        => (int) $row['id'],
            'chave'     => $row['chave'],
            'nome'      => $row['nome'],
            'descricao' => $row['descricao'] ?? null,
        ], $rows);
    }

    /**
     * IDs de permissões de um perfil
     */
    public function getPermissionIds(int $perfilId): array
    {
        $sql = "SELECT permissao_id FROM {$this->pivotTable} WHERE perfil_id = :perfil_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':perfil_id' => $perfilId]);

        return array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permissao_id'));
    }

    /**
     * Sincronizar permissões de um perfil
     */
    public function syncPermissions(int $perfilId, array $permissoesIds): void
    {
        $sqlDelete = "DELETE FROM {$this->pivotTable} WHERE perfil_id = :perfil_id";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->execute([':perfil_id' => $perfilId]);

        if (empty($permissoesIds)) {
            return;
        }

        $sqlInsert = "INSERT INTO {$this->pivotTable} (perfil_id, permissao_id) VALUES (:perfil_id, :permissao_id)";
        $stmtInsert = $this->db->prepare($sqlInsert);

        foreach ($permissoesIds as $permId) {
            $stmtInsert->execute([
                ':perfil_id'    => $perfilId,
                ':permissao_id' => (int) $permId,
            ]);
        }
    }
}