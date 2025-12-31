<?php
namespace App\Controllers;

use App\Core\BaseController;

class HomeController extends BaseController
{
    public function index(): void
    {
        echo $this->twig->render('home.twig', [
            'titulo' => 'Página Inicial'
        ]);
    }
}