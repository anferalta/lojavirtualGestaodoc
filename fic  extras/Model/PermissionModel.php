<?php
namespace app\Core;

use PDO;

class Permission
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        return $this->db->query("SELECT * FROM permissoes ORDER BY chave ASC")
                        ->fetchAll(PDO::FETCH_OBJ);
    }

    public function find(int $id): ?object
    {
        $stm = $this->db->prepare("SELECT * FROM permissoes WHERE id = :id");
        $stm->execute(['id' => $id]);
        return $stm->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO permissoes (chave, descricao) VALUES (:chave, :descricao)";
        $stm = $this->db->prepare($sql);
        return $stm->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE permissoes SET chave = :chave, descricao = :descricao WHERE id = :id";
        $stm = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stm->execute($data);
    }

    public function delete(int $id): bool
    {
        $stm = $this->db->prepare("DELETE FROM permissoes WHERE id = :id");
        return $stm->execute(['id' => $id]);
    }

    public function userHas(int $userId, string $permission): bool
    {
        $sql = "
            SELECT COUNT(*) 
            FROM perfis_permissoes pp
            INNER JOIN utilizadores u ON u.perfil_id = pp.perfil_id
            INNER JOIN permissoes p ON p.id = pp.permissao_id
            WHERE u.id = :uid AND p.chave = :perm
        ";

        $stm = $this->db->prepare($sql);
        $stm->execute([
            'uid'  => $userId,
            'perm' => $permission
        ]);

        return (int)$stm->fetchColumn() > 0;
    }
}