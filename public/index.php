<?php

// Iniciar sessão (Sessao::start será chamado no BaseController)

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Configurações globais
require BASE_PATH . '/app/config.php';
require BASE_PATH . '/app/bootstrap/session.php';

// Iniciar router
$router = new \App\Core\Router();
require __DIR__ . '/../routes/web.php';
$router->dispatch();

