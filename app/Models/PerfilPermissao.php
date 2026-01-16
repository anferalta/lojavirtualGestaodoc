<?php

namespace App\Models;

use App\Core\Model;

class PerfilPermissao extends Model
{
    protected static string $tabela = 'perfil_permissao';

    public int $perfil_id;
    public int $permissao_id;

    public function allGrouped(): array
    {
        $sql = "SELECT perfil_id, permissao_id FROM perfil_permissao";
        $stmt = \App\Core\Database::getConexao()->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];

        foreach ($rows as $row) {
            $result[$row['perfil_id']][] = $row['permissao_id'];
        }

        return $result;
    }
}