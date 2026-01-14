<?php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Models\Utilizador;

class DashboardAdminController extends BaseController
{
    public function index()
    {
        $total = Utilizador::count();
        $ativos = Utilizador::countWhere("estado = 1");
        $inativos = Utilizador::countWhere("estado = 0");

        return $this->view('admin/dashboard/index', compact('total', 'ativos', 'inativos'));
    }
}