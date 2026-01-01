<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use App\Core\Sessao;
use App\Core\Acl;
use App\Core\Helpers;

class TwigBootstrap {

    public static function init(): Environment {
        // Caminho correto para as views
        $loader = new FilesystemLoader(BASE_PATH . '/app/Views');

        // Ambiente Twig
        $twig = new Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);

        /**
         * ---------------------------------------------------------
         * FUNÇÕES TWIG
         * ---------------------------------------------------------
         */
        // URL helper
        $twig->addFunction(new TwigFunction('url', function ($path = '') {
                            return Helpers::url($path);
                        }));

        // Asset helper
        $twig->addFunction(new TwigFunction('asset', function ($path = '') {
                            return Helpers::asset($path);
                        }));

        // CSRF token
        $twig->addFunction(new TwigFunction('csrf', function () {
                            return Sessao::csrf();
                        }));

        // Flash message
        $twig->addFunction(new TwigFunction('flash', function () {
                            return Sessao::flash();
                        }));

        // ACL: permissões
        $twig->addFunction(new TwigFunction('can', function ($perm) {
                            return Helpers::can($perm);
                        }));

        /**
         * ---------------------------------------------------------
         * VARIÁVEIS GLOBAIS
         * ---------------------------------------------------------
         */
        // URI atual (para menus ativos)
        $twig->addGlobal('app', [
            'request' => [
                'uri' => $_SERVER['REQUEST_URI'] ?? '/'
            ]
        ]);

        return $twig;
    }

    protected function view(string $template, array $data = []): void {
        $data['flash'] = Sessao::getFlash();
        echo $this->twig->render($template . '.twig', $data);
    }
}
