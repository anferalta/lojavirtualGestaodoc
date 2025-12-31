<?php

echo "Iniciando migração para produção...\n\n";

$root = __DIR__;
$public = $root . '/public';

if (!is_dir($public)) {
    mkdir($public, 0775, true);
    echo "Criado diretório /public\n";
}

$arquivosPublicos = [
    'index.php',
    '.htaccess',
    'favicon.ico',
];

foreach ($arquivosPublicos as $arquivo) {
    $origem = $root . '/' . $arquivo;
    $destino = $public . '/' . $arquivo;

    if (file_exists($origem)) {
        rename($origem, $destino);
        echo "Movido: $arquivo\n";
    }
}

$dirs = [
    'assets',
    'uploads_publicos',
];

foreach ($dirs as $dir) {
    if (!is_dir("$public/$dir")) {
        mkdir("$public/$dir", 0775, true);
        echo "Criado diretório: $dir\n";
    }
}

echo "\nMigração concluída com sucesso.\n";