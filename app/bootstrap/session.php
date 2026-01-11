<?php

// Domínio da aplicação
$domain = $_ENV['APP_DOMAIN'] ?? '';

// Se o domínio estiver vazio, não definir o parâmetro (evita cookies inválidos)
if (!empty($domain)) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $domain,
        'secure' => false, // true se usares HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

session_start();