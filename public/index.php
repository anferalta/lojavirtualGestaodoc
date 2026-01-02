<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Configurações globais
require BASE_PATH . '/app/config.php';

// Iniciar sessão (Sessao::start será chamado no BaseController)
session_start();

// Iniciar router
require BASE_PATH . '/router.php';