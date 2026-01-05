<?php

namespace App\Core;

use App\Core\TwigBootstrap;
use App\Core\Sessao;
use App\Core\Auth;
use App\Core\Notification;
use App\Core\Helpers;

abstract class BaseController {

    protected $twig;

    public function __construct() {
        // Iniciar sessão
        Sessao::start();

        // Iniciar Twig
        $this->twig = TwigBootstrap::init();

        /**
         * AUTH — closures para garantir que Auth::user() e Auth::check()
         * são sempre avaliados dinamicamente e nunca ficam em cache.
         */
        $this->twig->addGlobal('auth', [
            'check' => fn() => Auth::check(),
            'user'  => fn() => Auth::user()
        ]);

        /**
         * Nome do utilizador — variável simples (NÃO closure)
         * Assim evita o erro "Unknown function usuario_nome".
         */
        $this->twig->addGlobal('usuario_nome', Auth::user()->nome ?? '');

        /**
         * Notificações — lazy loading via closure
         */
        $this->twig->addGlobal('notificacoes_unread', fn() => Notification::unreadForCurrent());

        /**
         * ACL — closure para garantir avaliação dinâmica
         */
        $this->twig->addGlobal('acl', [
            'can' => fn($p) => Helpers::can($p)
        ]);

        /**
         * CSRF — variável global
         */
        $this->twig->addGlobal('csrf', Sessao::csrf());

        /**
         * Rota atual — útil para menus ativos
         */
        $route = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $this->twig->addGlobal('app_route', $route);
    }

    protected function view(string $template, array $data = []): void {
        $data['flash'] = Sessao::getFlash();
        echo $this->twig->render($template . '.twig', $data);
    }

    protected function redirect(string $url): void {
        header("Location: " . Helpers::url($url));
        exit;
    }
}