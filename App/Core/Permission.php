<?php
namespace App\Core;

use PDO;

class Permission
{
    public function __construct(private PDO $db) {}

    public function userHas(int $userId, string $permission): bool
    {
        $sql = "
            SELECT COUNT(*) 
            FROM perfis_permissoes pp
            INNER JOIN utilizadores u ON u.perfil_id = pp.perfil_id
            INNER JOIN permissoes p ON p.id = pp.permissao_id
            WHERE u.id = :uid AND p.chave = :perm
            LIMIT 1
        ";

        $stm = $this->db->prepare($sql);
        $stm->execute([
            'uid'  => $userId,
            'perm' => $permission
        ]);

        return (int)$stm->fetchColumn() > 0;
    }
}