<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigBootstrap
{
    public static function init(): Environment
    {
        $viewsPath = BASE_PATH . '/app/Views';

        $loader = new FilesystemLoader($viewsPath);

        $twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
            'auto_reload' => true,
        ]);

        // Função asset()
        $twig->addFunction(new TwigFunction('asset', function (string $path): string {
            return Helpers::url('/assets/' . ltrim($path, '/'));
        }));

        // Função url()
        $twig->addFunction(new TwigFunction('url', function (string $path = ''): string {
            return Helpers::url($path);
        }));

        return $twig;
    }
}