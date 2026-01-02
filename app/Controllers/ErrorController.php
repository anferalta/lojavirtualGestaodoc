<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ErrorController
{
    private Environment $twig;

    public function __construct()
    {
        // Twig mínimo, sem Bootstrap, sem ACL, sem Auth, sem Sessao
        $loader = new FilesystemLoader(BASE_PATH . '/App/Views');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => false
        ]);
    }

    public function error404(): void
    {
        http_response_code(404);
        echo $this->twig->render('errors/404.twig');
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

    public function error(int $codigo = 500): void
    {
        http_response_code($codigo);

        $view = "errors/{$codigo}.twig";

        if (!file_exists(BASE_PATH . "/App/Views/errors/{$codigo}.twig")) {
            $view = "errors/500.twig";
            $codigo = 500;
        }

        echo $this->twig->render($view, [
            'titulo' => "Erro {$codigo}",
            'codigo' => $codigo
        ]);
    }
}