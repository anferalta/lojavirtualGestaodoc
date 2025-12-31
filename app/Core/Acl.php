<?php

namespace app\Core;

use app\Core\Conexao;
use PDO;

class Acl
{
    public static function can(string $chave): bool
    {
        $user = Auth::user();
        if (!$user || !$user->perfil_id) {
            return false;
        }

        // Cache por sessão
        if (!isset($_SESSION['acl_cache'])) {
            self::carregarPermissoes($user->perfil_id);
        }

        return in_array($chave, $_SESSION['acl_cache'], true);
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

        $_SESSION['acl_cache'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'chave');
    }

    public static function flush(): void
    {
        unset($_SESSION['acl_cache']);
    }
}