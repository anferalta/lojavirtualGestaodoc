<?php
require_once __DIR__ . '/../app/Core/Env.php';
require_once __DIR__ . '/../app/Core/Conexao.php';

use app\Core\Env;
use app\Core\Conexao;

session_start();
Env::load();
$con = Conexao::getInstancia();

// Gera CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_GET['token'] ?? null;

// Valida token
$stmt = $con->prepare("SELECT id FROM usuarios WHERE reset_token = :token AND reset_expira > NOW()");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_OBJ);

if (!$user) {
    $_SESSION['erro'] = "Token inválido ou expirado.";
    header("Location: login.php");
    exit;
}

// Processa redefinição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? null;
    $senha = $_POST['senha'] ?? '';

    if (!$csrf || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $_SESSION['erro'] = "Falha de segurança: token inválido.";
    } elseif (strlen($senha) < 8) {
        $_SESSION['erro'] = "A senha deve ter pelo menos 8 caracteres.";
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $con->prepare("UPDATE usuarios 
                               SET senha = :senha, reset_token = NULL, reset_expira = NULL 
                               WHERE id = :id");
        $stmt->execute([':senha' => $hash, ':id' => $user->id]);

        $_SESSION['sucesso'] = "Senha redefinida com sucesso!";
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Redefinir Senha</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h1>Redefinir Senha</h1>

  <?php if (!empty($_SESSION['erro'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro']) ?></div>
    <?php unset($_SESSION['erro']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['sucesso'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
    <?php unset($_SESSION['sucesso']); ?>
  <?php endif; ?>

  <form method="post" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <div class="col-md-6">
      <label class="form-label">Nova senha</label>
      <input type="password" name="senha" class="form-control" required minlength="8">
    </div>

    <div class="col-12">
      <button class="btn btn-primary" type="submit">Redefinir</button>
      <a href="login.php" class="btn btn-secondary">Voltar ao Login</a>
    </div>
  </form>
</body>
</html>