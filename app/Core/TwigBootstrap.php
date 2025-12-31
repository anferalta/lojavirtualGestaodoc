<?php

namespace app\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigBootstrap
{
    public static function init(): Environment
    {
        $loader = new FilesystemLoader(BASE_PATH . '/views');

        $twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
        ]);

        // Função URL
        $twig->addFunction(new TwigFunction('url', function ($path) {
            return Helpers::url($path);
        }));

        // Função CSRF
        $twig->addFunction(new TwigFunction('csrf', function () {
            return Sessao::csrf();
        }));

        // Função ACL
        $twig->addFunction(new TwigFunction('can', function ($perm) {
            return Acl::can($perm);
        }));

        return $twig;
    }
}