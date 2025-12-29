<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;

class PainelController extends BaseController
{
    public function index(): void
    {
        $user = Auth::user();

        echo $this->twig->render('painel/index.twig', [
            'user' => $user
        ]);
    }
}