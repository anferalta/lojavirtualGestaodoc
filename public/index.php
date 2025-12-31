<?php

declare(strict_types=1);

session_start();

// 1) Autoload (sempre primeiro)
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Route;

// 2) Carregar variáveis de ambiente (.env)
Env::load();

// 3) Gerar token CSRF se não existir
if (!isset($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}

// 4) Carregar middlewares globais
require __DIR__ . '/../App/bootstrap/middlewares.php';

// 5) Carregar rotas
require __DIR__ . '/../App/Routes/web.php';

// 6) Despachar rota
Route::dispatch();