<?php
require_once __DIR__ . '/../App/Core/Env.php';
require_once __DIR__ . '/../App/Core/Conexao.php';

use App\Core\Env;
use App\Core\Conexao;

Env::load();
$con = Conexao::getInstancia();

$id      = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome    = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
$apelido = filter_input(INPUT_POST, 'apelido', FILTER_SANITIZE_STRING);
$mail    = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_EMAIL);
$level   = filter_input(INPUT_POST, 'level', FILTER_SANITIZE_STRING);
$estado  = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);
$cidade  = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING);
$nif     = filter_input(INPUT_POST, 'nif', FILTER_SANITIZE_STRING);

if (!$id || !$nome || !$mail) {
    header("Location: listar_usuarios.php?msg=erro");
    exit;
}

$sql = "UPDATE usuarios 
        SET nome = :nome, apelido = :apelido, mail = :mail, level = :level, 
            estado = :estado, cidade = :cidade, nif = :nif
        WHERE id = :id";

$stmt = $con->prepare($sql);
$sucesso = $stmt->execute([
    ':nome'    => $nome,
    ':apelido' => $apelido,
    ':mail'    => $mail,
    ':level'   => $level,
    ':estado'  => $estado,
    ':cidade'  => $cidade,
    ':nif'     => $nif,
    ':id'      => $id
]);

if ($sucesso) {
    header("Location: listar_usuarios.php?msg=sucesso");
} else {
    header("Location: listar_usuarios.php?msg=erro");
}
exit;