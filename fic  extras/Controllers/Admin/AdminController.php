<?php

namespace app\Controllers\Admin;

use app\Core\Controller;
use app\Core\Helpers;
use app\Controllers\UsuarioController;
use app\Core\Sessao;

class AdminController extends Controller
{
    protected $usuario;

    public function __construct()
    {
        parent::__construct('app/Views/Sites');

        // Corrigido: usar UsuarioController
        $this->usuario = UsuarioController::usuario();

        // Verificação de login e nível de acesso
        if (!$this->usuario || $this->usuario->level != 3) {
            $this->mensagem->erro('Faça login para acessar o painel de controle!')->flash();

            $sessao = new Sessao();
            $sessao->limpar('usuarioId');

            // Corrigido: redirecionar para rota, não para view
            Helpers::redirecionar('/site/login');
        }
    }
}