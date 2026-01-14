<?php

namespace App\Core;

use Twig\Environment;

abstract class BaseController {

    protected Environment $twig;

    public function __construct() {
        Sessao::start();
        $this->twig = TwigBootstrap::init();

        $this->injectGlobalVariables();
    }

    /**
     * Variáveis globais disponíveis em todas as views
     */
    private function injectGlobalVariables(): void {
        $this->twig->addGlobal('usuario_nome', Sessao::get('usuario_nome'));
        $this->twig->addGlobal('notificacoes_unread', Sessao::get('notificacoes_unread') ?? []);
        $this->twig->addGlobal('acl', new ACL());
        $this->twig->addGlobal('app_route', $_SERVER['REQUEST_URI'] ?? '/');

        // ⭐ CSRF disponível em todas as views
        $this->twig->addGlobal('csrf', Sessao::csrf());
    }

    protected function view(string $template, array $data = []): void {
        if (!str_ends_with($template, '.twig')) {
            $template .= '.twig';
        }

        // Flash messages disponíveis em todas as views
        $data['flash'] = Sessao::getFlash();

        echo $this->twig->render($template, $data);
    }

    protected function redirect(string $url): void {
        header("Location: " . url($url));
        exit;
    }
}
