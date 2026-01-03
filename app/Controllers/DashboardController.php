<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Utilizador;
use App\Models\Documento;
use App\Models\Perfil;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $stats = [
            'utilizadores' => (new Utilizador())->count(),
            'documentos'   => (new Documento())->count(),
            'perfis'       => (new Perfil())->count(),
            'ativos'       => (new Utilizador())->where('estado', '=', 1)->count(),
        ];

        $this->view('dashboard/index', [
            'stats' => $stats
        ]);
    }
}