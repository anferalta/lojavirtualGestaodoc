<?php

namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;
use App\Core\Sessao;
use App\Core\Menu;
use App\Core\Helpers;

class BaseController {

    protected Environment $twig;

    public function __construct() {
        // Sessão sempre ativa
        Sessao::start();

        // Loader do Twig
        $loader = new FilesystemLoader(__DIR__ . '/../Views');

        // Ambiente Twig
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);
        
        $this->twig = TwigBootstrap::init();

        $this->twig->addGlobal('auth_user', Auth::user());
        $this->twig->addGlobal('notificacoes_nao_lidas', Notification::unreadForCurrent());

        /**
         * ---------------------------------------------------------
         * FUNÇÕES TWIG
         * ---------------------------------------------------------
         */
        // URL helper
        $this->twig->addFunction(new TwigFunction('url', function ($path = '') {
                            return Helpers::url($path);
                        }));

        // Asset helper
        $this->twig->addFunction(new TwigFunction('asset', function ($path = '') {
                            return Helpers::asset($path);
                        }));

        // CSRF token
        $this->twig->addFunction(new TwigFunction('csrf', function () {
                            return Sessao::csrf();
                        }));

        // Flash message (ler e apagar)
        $this->twig->addFunction(new TwigFunction('flash', function () {
                            return Sessao::flash();
                        }));

        // Permissões (ACL)
        $this->twig->addFunction(new TwigFunction('can', function ($perm) {
                            return Helpers::can($perm);
                        }));

        /**
         * ---------------------------------------------------------
         * VARIÁVEIS GLOBAIS SEGURAS
         * ---------------------------------------------------------
         */
        // Menu dinâmico
        $this->twig->addGlobal('menu_service', new Menu());

        // Nome do utilizador autenticado
        $this->twig->addGlobal('usuario_nome', $_SESSION['usuario_nome'] ?? null);
    }

    protected function view(string $template, array $data = []): void {
        echo $this->twig->render($template . '.twig', $data);
    }
}
