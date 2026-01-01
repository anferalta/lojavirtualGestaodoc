<?php

namespace App\Controllers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;
use App\Core\Sessao;
use App\Core\Helpers;
use App\Core\Menu;

class BaseController {

    protected Environment $twig;

    public function __construct() {
        // Iniciar sessão
        Sessao::start();

        // Carregar views
        $loader = new FilesystemLoader(__DIR__ . '/../../views');

        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);

        /*
          |--------------------------------------------------------------------------
          | Funções Twig
          |--------------------------------------------------------------------------
         */

        // url()
        $this->twig->addFunction(new TwigFunction('url', function ($path = '') {
                            return Helpers::url($path);
                        }));

        // asset()
        $this->twig->addFunction(new TwigFunction('asset', function ($path = '') {
                            return Helpers::asset($path);
                        }));

        // csrf()
        $this->twig->addFunction(new TwigFunction('csrf', function () {
                            return Sessao::csrf();
                        }));

        /*
          |--------------------------------------------------------------------------
          | Variáveis Globais Twig
          |--------------------------------------------------------------------------
         */

        // Menu lateral
        $this->twig->addGlobal('menu_service', new Menu());

        // Nome do utilizador autenticado
        $this->twig->addGlobal('usuario_nome', Sessao::get('usuario_nome'));

        // ⭐ Flash message (ler e limpar)
        $this->twig->addGlobal('flash', Sessao::getFlash());
    }

    protected function view(string $template, array $data = []): void {
        echo $this->twig->render($template . '.twig', $data);
    }
}
