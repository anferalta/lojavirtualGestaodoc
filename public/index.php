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
require BASE_PATH . '/app/Routes/web.php';

\App\Core\Route::dispatch();