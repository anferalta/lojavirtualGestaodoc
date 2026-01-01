<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Conexao;

$permissoes = [
    ['utilizadores.ver', 'Ver utilizadores', 'Utilizadores'],
    ['utilizadores.criar', 'Criar utilizadores', 'Utilizadores'],
    ['utilizadores.editar', 'Editar utilizadores', 'Utilizadores'],
    ['utilizadores.eliminar', 'Eliminar utilizadores', 'Utilizadores'],

    ['perfis.ver', 'Ver perfis', 'Perfis'],
    ['perfis.criar', 'Criar perfis', 'Perfis'],
    ['perfis.editar', 'Editar perfis', 'Perfis'],
    ['perfis.eliminar', 'Eliminar perfis', 'Perfis'],

    ['permissoes.ver', 'Ver permissões', 'Permissões'],
    ['permissoes.criar', 'Criar permissões', 'Permissões'],
    ['permissoes.editar', 'Editar permissões', 'Permissões'],
    ['permissoes.eliminar', 'Eliminar permissões', 'Permissões'],

    ['documentos.ver', 'Ver documentos', 'Documentos'],
    ['documentos.criar', 'Carregar documentos', 'Documentos'],
    ['documentos.editar', 'Editar documentos', 'Documentos'],
    ['documentos.eliminar', 'Eliminar documentos', 'Documentos'],
    ['documentos.download', 'Download de documentos', 'Documentos'],
];

$db = Conexao::getInstancia();

foreach ($permissoes as $p) {
    [$chave, $nome, $categoria] = $p;

    $stmt = $db->prepare("INSERT IGNORE INTO permissoes (chave, nome, categoria)
                          VALUES (:chave, :nome, :categoria)");

    $stmt->execute([
        ':chave'     => $chave,
        ':nome'      => $nome,
        ':categoria' => $categoria,
    ]);

    echo "Criada permissão: $chave\n";
}

echo "\nPermissões geradas com sucesso.\n";