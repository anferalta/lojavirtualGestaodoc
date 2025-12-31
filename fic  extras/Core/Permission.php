<?php

namespace app\Core;

use PDO;

class Permission
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Verifica se o utilizador tem uma permissão
     * - Admin técnico (nivel 3) tem tudo
     * - Caso contrário, verifica permissões do perfil
     */
    public function userHas(int $userId, string $permissao): bool
    {
        // 1. Obter dados do utilizador
        $sql = "SELECT nivel, perfil_id 
                FROM utilizadores 
                WHERE id = :id 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$user) {
            return false;
        }

        // 2. Admin técnico tem tudo
        if ((int)$user->nivel === 3) {
            return true;
        }

        // 3. Verificar permissões do perfil (via permissao_id → permissoes.chave)
        $sql = "SELECT COUNT(*) AS total
                FROM perfis_permissoes pp
                INNER JOIN permissoes p ON p.id = pp.permissao_id
                WHERE pp.perfil_id = :perfil
                  AND p.chave = :perm";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':perfil' => $user->perfil_id,
            ':perm'   => $permissao
        ]);

        return $stmt->fetch()->total > 0;
    }
}