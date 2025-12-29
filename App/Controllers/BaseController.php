<?php

namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;
use App\Core\Sessao;
use App\Core\Menu;

class BaseController
{
    protected Environment $twig;
    protected Sessao $sessao;

    public function __construct()
    {
        $this->sessao = new Sessao();

        $loader = new FilesystemLoader(__DIR__ . '/../../views');

        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);

        // Funções Twig
        $this->twig->addFunction(new TwigFunction('url', function ($path = '') {
            return \App\Core\Helpers::url($path);
        }));

        $this->twig->addFunction(new TwigFunction('asset', function ($path = '') {
            return \App\Core\Helpers::asset($path);
        }));

        $this->twig->addFunction(new TwigFunction('csrf', function () {
            return \App\Core\Sessao::csrf();
        }));

        // Globais
        $this->twig->addGlobal('menu_service', new Menu());
        $this->twig->addGlobal('usuario_nome', $_SESSION['usuario_nome'] ?? null);
    }
}