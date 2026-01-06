<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigBootstrap {

    public static function init(): Environment {
        $viewsPath = BASE_PATH . '/app/Views';

        $loader = new FilesystemLoader($viewsPath);

        $twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
            'auto_reload' => true,
        ]);

        // Função asset()
        $twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
                            return Helpers::url('/assets/' . ltrim($path, '/'));
                        }));

        // Função url()
        $twig->addFunction(new \Twig\TwigFunction('url', function ($path = '') {
                            return Helpers::url($path);
                        }));

        return $twig;
    }
}
