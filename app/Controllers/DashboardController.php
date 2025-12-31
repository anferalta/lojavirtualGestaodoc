<?php

namespace app\Controllers;

use app\Core\BaseController;
use app\Core\Conexao;
use app\Core\Auth;
use PDO;

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

        echo $this->twig->render('dashboard/index.twig', [
            'user'  => Auth::user(),
            'stats' => $stats,
        ]);
    }
}