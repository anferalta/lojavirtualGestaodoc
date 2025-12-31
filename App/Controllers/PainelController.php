<?php

namespace app\Controllers;

use app\Core\BaseController;
use app\Core\Auth;
use app\Core\Conexao;
use app\Models\Utilizador;
use app\Models\Documento;
use app\Models\Perfil;

class PainelController extends BaseController
{
    private Utilizador $userModel;
    private Documento $docModel;
    private Perfil $perfilModel;

    public function __construct()
    {
        parent::__construct();

        $db = Conexao::getInstancia();

        $this->userModel   = new Utilizador($db);
        $this->docModel    = new Documento($db);
        $this->perfilModel = new Perfil($db);
    }

    public function index(): void
    {
        $user = Auth::user();

        $totalUtilizadores = $this->userModel->count();
        $totalDocumentos   = $this->docModel->count();
        $totalPerfis       = $this->perfilModel->count();

        $this->view('painel/dashboard', [
            'titulo'             => 'Dashboard',
            'total_utilizadores' => $totalUtilizadores,
            'total_documentos'   => $totalDocumentos,
            'total_perfis'       => $totalPerfis
        ]);
    }
}