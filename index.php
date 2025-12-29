<?php
session_start();

require __DIR__ . '/vendor/autoload.php';   // 1) Autoload primeiro

use App\Core\Env;
use App\Core\Route;

Env::load();

// CSRF
if (!isset($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}

// 2) Carregar middlewares
require __DIR__ . '/App/bootstrap/middlewares.php';

// 3) Carregar rotas
require __DIR__ . '/App/Routes/web.php';

// 4) Despachar
Route::dispatch();