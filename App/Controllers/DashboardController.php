<?php

namespace App\Controllers;

use App\Core\BaseController;

class DashboardController extends BaseController
{
    public function index(): void
    {
        echo $this->twig->render('dashboard.twig', [
            'titulo' => 'Painel de Controlo'
        ]);
    }
}