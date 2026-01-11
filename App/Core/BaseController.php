<?php

namespace App\Core;

use Twig\Environment;

abstract class BaseController
{
    protected Environment $twig;

    public function __construct()
    {
        Sessao::start();
        $this->twig = TwigBootstrap::init();
    }

    protected function view(string $template, array $data = []): void
    {
        if (!str_ends_with($template, '.twig')) {
            $template .= '.twig';
        }

        // Flash messages disponíveis em todas as views
        $data['flash'] = Sessao::getFlash();

        echo $this->twig->render($template, $data);
    }
}