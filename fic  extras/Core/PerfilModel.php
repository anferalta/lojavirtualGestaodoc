<?php
namespace App\Core;

use PDO;

class Perfil
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        return $this->db->query("SELECT * FROM perfis ORDER BY nome ASC")
                        ->fetchAll(PDO::FETCH_OBJ);
    }

    public function find(int $id): ?object
    {
        $stm = $this->db->prepare("SELECT * FROM perfis WHERE id = :id");
        $stm->execute(['id' => $id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO perfis (nome, descricao, estado, criado_em)
                VALUES (:nome, :descricao, :estado, NOW())";

        $stm = $this->db->prepare($sql);
        return $stm->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE perfis SET nome = :nome, descricao = :descricao, estado = :estado,
                atualizado_em = NOW() WHERE id = :id";

        $stm = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stm->execute($data);
    }

    public function delete(int $id): bool
    {
        $stm = $this->db->prepare("DELETE FROM perfis WHERE id = :id");
        return $stm->execute(['id' => $id]);
    }

    public function getPermissions(int $perfilId): array
    {
        $sql = "
            SELECT p.* 
            FROM perfis_permissoes pp
            INNER JOIN permissoes p ON p.id = pp.permissao_id
            WHERE pp.perfil_id = :id
        ";

        $stm = $this->db->prepare($sql);
        $stm->execute(['id' => $perfilId]);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    public function syncPermissions(int $perfilId, array $permissoes): void
    {
        // Apagar permissões antigas
        $stm = $this->db->prepare("DELETE FROM perfis_permissoes WHERE perfil_id = :id");
        $stm->execute(['id' => $perfilId]);

        // Inserir novas
        $sql = "INSERT INTO perfis_permissoes (perfil_id, permissao_id) VALUES (:perfil, :perm)";
        $stm = $this->db->prepare($sql);

        foreach ($permissoes as $perm) {
            $stm->execute([
                'perfil' => $perfilId,
                'perm'   => $perm
            ]);
        }
    }
}