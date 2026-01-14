<?php

use App\Core\Database;

class AdminSeeder
{
    public static function run()
    {
        $db = Database::get();

        // Criar perfil admin
        $db->exec("
            INSERT INTO perfis (nome) VALUES ('Administrador')
        ");

        $perfilId = $db->lastInsertId();

        // Permissões do módulo utilizadores
        $permissoes = [
            'utilizadores.ver',
            'utilizadores.criar',
            'utilizadores.editar',
            'utilizadores.apagar'
        ];

        foreach ($permissoes as $p) {
            $stmt = $db->prepare("INSERT INTO permissoes (codigo) VALUES (?)");
            $stmt->execute([$p]);

            $permissaoId = $db->lastInsertId();

            // Associar ao perfil admin
            $stmt = $db->prepare("
                INSERT INTO perfil_permissoes (perfil_id, permissao_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$perfilId, $permissaoId]);
        }
    }
}