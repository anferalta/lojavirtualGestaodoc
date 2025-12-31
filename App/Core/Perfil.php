<?php

namespace app\Core;

use PDO;

class Perfil
{
    private PDO $db;
    private string $table = 'perfis';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY nome";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public function find(int $id): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $registo = $stmt->fetch(PDO::FETCH_OBJ);

        return $registo ?: null;
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
        $sql = "UPDATE {$this->table}
                SET nome = :nome,
                    descricao = :descricao,
                    estado = :estado
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'        => $id,
            ':nome'      => $dados['nome'],
            ':descricao' => $dados['descricao'] ?? null,
            ':estado'    => $dados['estado'] ?? 'ativo',
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    public function getPermissionIds(int $perfilId): array
    {
        $sql = "SELECT permissao_id FROM perfis_permissoes WHERE perfil_id = :perfil";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':perfil' => $perfilId]);

        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permissao_id');
    }

    public function syncPermissions(int $perfilId, array $permissionIds): void
    {
        $this->db->beginTransaction();

        $sqlDel = "DELETE FROM perfis_permissoes WHERE perfil_id = :perfil";
        $stmtDel = $this->db->prepare($sqlDel);
        $stmtDel->execute([':perfil' => $perfilId]);

        if (!empty($permissionIds)) {
            $sqlIns = "INSERT INTO perfis_permissoes (perfil_id, permissao_id)
                       VALUES (:perfil, :perm)";
            $stmtIns = $this->db->prepare($sqlIns);

            foreach ($permissionIds as $permId) {
                $stmtIns->execute([
                    ':perfil' => $perfilId,
                    ':perm'   => $permId,
                ]);
            }
        }

        $this->db->commit();
    }
}