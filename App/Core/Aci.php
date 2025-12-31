<?php

namespace App\Core;

use App\Core\Conexao;
use PDO;

class Acl
{
    private static ?array $permissoes = null;

    public static function can(string $chave): bool
    {
        $user = Auth::user();
        if (!$user || !$user->perfil_id) {
            return false;
        }

        if (self::$permissoes === null) {
            self::carregarPermissoes($user->perfil_id);
        }

        return in_array($chave, self::$permissoes, true);
    }

    private static function carregarPermissoes(int $perfilId): void
    {
        $db = Conexao::getInstancia();

        $sql = "SELECT p.chave
                FROM permissoes p
                INNER JOIN perfis_permissoes pp ON pp.permissao_id = p.id
                WHERE pp.perfil_id = :perfil";

        $stmt = $db->prepare($sql);
        $stmt->execute([':perfil' => $perfilId]);

        self::$permissoes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'chave');
    }

    public static function flush(): void
    {
        self::$permissoes = null;
    }
}