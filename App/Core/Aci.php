<?php

namespace App\Core;

use App\Core\Conexao;
use PDO;

class Acl
{
    private static ?PDO $db = null;

    private static function db(): PDO
    {
        if (!self::$db) {
            self::$db = Conexao::getInstancia();
        }
        return self::$db;
    }

    /**
     * Verifica se um perfil tem uma determinada permissão (por chave).
     */
    public static function perfilHasPermission(int $perfilId, string $permissionKey): bool
    {
        $sql = "SELECT 1
                FROM perfis_permissoes pp
                INNER JOIN permissoes p ON p.id = pp.permissao_id
                WHERE pp.perfil_id = :perfil_id
                  AND p.chave = :chave
                LIMIT 1";

        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            ':perfil_id' => $perfilId,
            ':chave'     => $permissionKey,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica se o utilizador atual (Auth) tem a permissão.
     */
    public static function can(string $permissionKey): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Se tiver perfil_id, usa perfil
        if (!empty($user->perfil_id)) {
            return self::perfilHasPermission((int) $user->perfil_id, $permissionKey);
        }

        // Fallback simples baseado em nível (se quiseres manter)
        if (isset($user->nivel)) {
            // Exemplo:
            if ($user->nivel >= 3) {
                return true; // admin
            }
        }

        return false;
    }
}