<?php
require_once __DIR__ . '/../App/Core/Env.php';
require_once __DIR__ . '/../App/Core/Conexao.php';

use App\Core\Env;
use App\Core\Conexao;

session_start();

Env::load();
$con = Conexao::getInstancia();

$token = $_GET['token'] ?? null;

if (!$token) {
    $_SESSION['erro'] = "Token não fornecido.";
    header("Location: login.php");
    exit;
}

$stmt = $con->prepare("SELECT id, reset_token, reset_expira FROM usuarios WHERE reset_token = :token");
$stmt->bindValue(':token', $token);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_OBJ);

if (!$user || strtotime($user->reset_expira) < time()) {
    $_SESSION['erro'] = "Token inválido ou expirado.";
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (strlen($senha) < 8) {
        $_SESSION['erro'] = "A senha deve ter pelo menos 8 caracteres.";
    } elseif ($senha !== $confirmar) {
        $_SESSION['erro'] = "As senhas não coincidem.";
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $con->prepare("UPDATE usuarios 
                               SET senha = :senha, reset_token = NULL, reset_expira = NULL 
                               WHERE id = :id");
        $stmt->bindValue(':senha', $hash);
        $stmt->bindValue(':id', $user->id);
        $stmt->execute();

        $_SESSION['sucesso'] = "Senha redefinida com sucesso! Já pode fazer login.";
        header("Location: login.php");
        exit;
    }
}