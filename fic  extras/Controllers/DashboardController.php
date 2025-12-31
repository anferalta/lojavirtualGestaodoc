<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use App\Core\Usuario;

class DashboardController extends BaseController
{
    private Usuario $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new Usuario(Conexao::getInstancia());
    }

    /**
     * Página inicial do painel
     */
    public function index(): void
    {
        // Estatísticas principais
        $totalUtilizadores = $this->userModel->count();

        // Últimos utilizadores (5 mais recentes)
        $ultimosUtilizadores = $this->userModel->paginate(5, 0);

        echo $this->twig->render('dashboard.twig', [
            'totalUtilizadores'   => $totalUtilizadores,
            'ultimosUtilizadores' => $ultimosUtilizadores
        ]);
    }
}