<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Sessao;

class PerfilController extends BaseController
{
    public function index(): void
    {
        Sessao::start();

        $this->view('perfil/index', [
            'user' => Auth::user(),
            'csrf' => Sessao::csrf()
        ]);
    }
}