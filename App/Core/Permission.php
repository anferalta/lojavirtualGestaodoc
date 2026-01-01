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

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY categoria, nome";
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

    public function findByKey(string $chave): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE chave = :chave LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':chave' => $chave]);

        $registo = $stmt->fetch(PDO::FETCH_OBJ);

        return $registo ?: null;
    }

    public function create(array $dados): bool
    {
        $sql = "INSERT INTO {$this->table} (chave, nome, categoria)
                VALUES (:chave, :nome, :categoria)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':chave'     => $dados['chave'],
            ':nome'      => $dados['nome'],
            ':categoria' => $dados['categoria'] ?? null,
        ]);
    }

    public function update(int $id, array $dados): bool
    {
        $sql = "UPDATE {$this->table}
                SET chave = :chave, nome = :nome, categoria = :categoria
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'        => $id,
            ':chave'     => $dados['chave'],
            ':nome'      => $dados['nome'],
            ':categoria' => $dados['categoria'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    public function getByPerfil(int $perfilId): array
    {
        $sql = "SELECT p.*
                FROM {$this->table} p
                INNER JOIN perfis_permissoes pp ON pp.permissao_id = p.id
                WHERE pp.perfil_id = :perfil";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':perfil' => $perfilId]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getIdsByPerfil(int $perfilId): array
    {
        $sql = "SELECT permissao_id FROM perfis_permissoes WHERE perfil_id = :perfil";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':perfil' => $perfilId]);

        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permissao_id');
    }

    public function syncToPerfil(int $perfilId, array $permissionIds): void
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