if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    $csrf      = $_POST['csrf_token'] ?? '';

    if (!$csrf || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $erros[] = "Falha de segurança: token inválido.";
    } elseif (strlen($novaSenha) < 8) {
        $erros[] = "A senha deve ter pelo menos 8 caracteres.";
    } elseif ($novaSenha !== $confirmar) {
        $erros[] = "As senhas não coincidem.";
    }

    if (empty($erros)) {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios SET senha = :senha WHERE id = :id";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(':senha', $hash);
        $stmt->bindValue(':id', $id);

        if ($stmt->execute()) {
            unset($_SESSION['csrf_token']); // invalida token usado
            $_SESSION['sucesso'] = "Senha atualizada com sucesso!";
            header("Location: listar_usuarios.php");
            exit;
        } else {
            $erros[] = "Erro ao atualizar a senha.";
        }
    }
}