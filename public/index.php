<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

require BASE_PATH . '/app/config.php';
require BASE_PATH . '/app/bootstrap/session.php';

$router = new \App\Core\Router();
require BASE_PATH . '/routes/web.php';

$router->dispatch();