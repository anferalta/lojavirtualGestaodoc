<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Twig\TwigFilter;

class TwigBootstrap {

    public static function init(): Environment {
        // Diretório das views
        $loader = new FilesystemLoader(__DIR__ . '/../Views');

        // Configuração do Twig
        $twig = new Environment($loader, [
            'cache' => false, // podes ativar depois: __DIR__ . '/../../storage/cache/twig'
            'debug' => true,
            'auto_reload' => true,
        ]);

        /*
          |--------------------------------------------------------------------------
          | Funções Twig Globais (helpers)
          |--------------------------------------------------------------------------
         */

        // URL helper
        $twig->addFunction(new TwigFunction('url', function ($path = '') {
                            return Helpers::url($path);
                        }));

        // Asset helper
        $twig->addFunction(new TwigFunction('asset', function ($path) {
                            return Helpers::asset($path);
                        }));

        // ACL helper
        $twig->addFunction(new TwigFunction('can', function ($permission) {
                            return Helpers::can($permission);
                        }));

        // Notificações (função real, não closure global)
        $twig->addFunction(new TwigFunction('notificacoes_unread', function () {
                            return Notification::unreadForCurrent();
                        }));

        // Dump (debug)
        $twig->addFunction(new TwigFunction('dump', function ($var) {
                            dump($var);
                        }));

        $twig->addFunction(new \Twig\TwigFunction('csrf', function () {
                            return \App\Core\Sessao::csrf();
                        }));

        /*
          |--------------------------------------------------------------------------
          | Filtros Twig (opcional)
          |--------------------------------------------------------------------------
         */

        $twig->addFilter(new TwigFilter('upper', fn($v) => strtoupper($v)));
        $twig->addFilter(new TwigFilter('lower', fn($v) => strtolower($v)));

        /*
          |--------------------------------------------------------------------------
          | Variáveis Globais
          |--------------------------------------------------------------------------
         */

        // Sessão flash
        $twig->addGlobal('flash', Sessao::getFlash());

        // Auth
        $twig->addGlobal('auth', [
            'check' => Auth::check(),
            'user' => Auth::user()
        ]);

        // Nome do utilizador
        $twig->addGlobal('usuario_nome', Auth::user()->nome ?? '');

        // ACL
        $twig->addGlobal('acl', [
            'can' => fn($p) => Helpers::can($p)
        ]);

        // Rota atual
        $twig->addGlobal('app_route', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

        return $twig;
    }
}
