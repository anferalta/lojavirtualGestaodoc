<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../index.php';

use app\Core\Conexao;

$db = Conexao::getInstancia();

$email = 'admin@admin.com';

// Verificar se já existe
$check = $db->prepare("SELECT id FROM utilizadores WHERE email = :email");
$check->execute(['email' => $email]);

if ($check->fetch()) {
    echo "O utilizador admin já existe.";
    exit;
}

// Criar admin
$senha = 'admin123';
$hash  = password_hash($senha, PASSWORD_DEFAULT);

$sql = "INSERT INTO utilizadores (nome, email, senha, nivel, estado)
        VALUES (:nome, :email, :senha, :nivel, :estado)";

$stmt = $db->prepare($sql);

$stmt->execute([
    'nome'   => 'Administrador',
    'email'  => $email,
    'senha'  => $hash,
    'nivel'  => 10,
    'estado' => 'ativo'
]);

echo "Utilizador admin criado com sucesso!";
exit;