<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

session_start();

// Core
require BASE_PATH . '/app/Core/Conexao.php';
require BASE_PATH . '/app/Core/Helpers.php';
require BASE_PATH . '/app/Core/Auth.php';
require BASE_PATH . '/app/Core/Acl.php';
require BASE_PATH . '/app/Core/Sessao.php';
require BASE_PATH . '/app/Core/TwigBootstrap.php';

// Router
require BASE_PATH . '/router.php';