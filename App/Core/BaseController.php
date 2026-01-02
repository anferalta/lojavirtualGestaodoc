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
        Sessao::start();

        $this->twig = TwigBootstrap::init();

        // AUTH
        $this->twig->addGlobal('auth', [
            'check' => Auth::check(),
            'user'  => Auth::user()
        ]);

        // NOME DO UTILIZADOR
        $this->twig->addGlobal('usuario_nome', Auth::user()->nome ?? '');

        // NOTIFICAÇÕES (lazy loading sem arrays com closures)
        $this->twig->addGlobal('notificacoes_unread', fn() => Notification::unreadForCurrent());

        // ACL
        $this->twig->addGlobal('acl', [
            'can' => fn($p) => Helpers::can($p)
        ]);

        // ROTA ATUAL (para menus ativos)
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