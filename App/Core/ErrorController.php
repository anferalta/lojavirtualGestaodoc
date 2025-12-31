<?php

namespace App\Controllers;

use App\Core\BaseController;

class ErrorController extends BaseController
{
    public function error404(): void
    {
        http_response_code(404);

        echo $this->twig->render('errors/404.twig', [
            'titulo' => 'Página não encontrada',
            'codigo' => 404
        ]);
    }

    public function error403(): void
    {
        http_response_code(403);

        echo $this->twig->render('errors/403.twig', [
            'titulo' => 'Acesso negado',
            'codigo' => 403
        ]);
    }

    public function error500(): void
    {
        http_response_code(500);

        echo $this->twig->render('errors/500.twig', [
            'titulo' => 'Erro interno do servidor',
            'codigo' => 500
        ]);
    }

    /**
     * Fallback genérico (opcional)
     */
    public function error(int $codigo = 500): void
    {
        http_response_code($codigo);

        $view = "errors/{$codigo}.twig";

        if (!file_exists(__DIR__ . "/../../views/errors/{$codigo}.twig")) {
            $view = "errors/500.twig";
            $codigo = 500;
        }

        echo $this->twig->render($view, [
            'titulo' => "Erro {$codigo}",
            'codigo' => $codigo
        ]);
    }
}