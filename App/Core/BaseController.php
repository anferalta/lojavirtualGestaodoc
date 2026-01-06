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

        // A partir daqui, $this->twig NÃO pode ser null
        $this->twig->addGlobal('auth', [
            'check' => fn() => Auth::check(),
            'user' => fn() => Auth::user()
        ]);

        $this->twig->addGlobal('usuario_nome', Auth::user()->nome ?? '');

        $this->twig->addGlobal('notificacoes_unread', fn() => Notification::unreadForCurrent());

        $this->twig->addGlobal('acl', [
            'can' => fn($p) => Helpers::can($p)
        ]);

        $this->twig->addGlobal('csrf', Sessao::csrf());

        $route = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $this->twig->addGlobal('app_route', $route);

        // Se quiseres adicionar env:
        $this->twig->addGlobal('env', [
            'UPLOAD_ALLOWED_EXT' => $_ENV['UPLOAD_ALLOWED_EXT'] ?? '',
            'UPLOAD_MAX_SIZE' => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 0),
        ]);
    }

    protected function view(string $template, array $data = []): void {
        if (!str_ends_with($template, '.twig')) {
            $template .= '.twig';
        }

        $data['flash'] = Sessao::getFlash();
        echo $this->twig->render($template, $data);
    }

    protected function redirect(string $url): void {
        header("Location: " . Helpers::url($url));
        exit;
    }
}
