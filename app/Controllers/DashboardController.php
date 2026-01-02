<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $db = Conexao::getInstancia();

        $stats = [
            'utilizadores' => (int)$db->query("SELECT COUNT(*) FROM utilizadores")->fetchColumn(),
            'documentos'   => (int)$db->query("SELECT COUNT(*) FROM documentos")->fetchColumn(),
            'ativos'       => (int)$db->query("SELECT COUNT(*) FROM utilizadores WHERE estado = 1")->fetchColumn(),
        ];

        $this->view('dashboard/index', [
            'stats' => $stats
        ]);
    }
}