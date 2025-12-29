<?php
namespace App\Controllers;

use App\Core\BaseController;

class ErrorController extends BaseController
{
    public function notFound(): void
    {
        http_response_code(404);
        echo $this->twig->render('errors/404.twig', [
            'titulo' => 'Página não encontrada'
        ]);
    }

    public function forbidden(): void
    {
        http_response_code(403);
        echo $this->twig->render('errors/403.twig', [
            'titulo' => 'Acesso negado'
        ]);
    }
}