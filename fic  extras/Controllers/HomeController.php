<?php
namespace app\Controllers;

use app\Core\BaseController;

class HomeController extends BaseController
{
    public function index(): void
    {
        echo $this->twig->render('home.twig', [
            'titulo' => 'Página Inicial'
        ]);
    }
}