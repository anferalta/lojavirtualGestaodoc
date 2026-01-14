<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Documento;
use App\Models\Perfil;
use App\Models\Utilizador;

class DashboardController extends BaseController
{
    public function index()
    {
        // Obter ID do utilizador autenticado
        $id = $_SESSION['user_id'] ?? null;

        // Buscar utilizador
        $usuario = $id ? (new Utilizador())->find($id) : null;

        // Nome do utilizador
        $usuario_nome = $usuario->nome ?? 'Utilizador';

        // Estatísticas principais
        $total_utilizadores = Utilizador::count();
        $total_documentos   = Documento::count();
        $total_perfis       = Perfil::count();

        // Estatísticas avançadas
        $documentos_hoje = Documento::countHoje();
        $documentos_mes  = Documento::countMes();

        return $this->view('painel/dashboard', [
            'usuario_nome'       => $usuario_nome,
            'total_utilizadores' => $total_utilizadores,
            'total_documentos'   => $total_documentos,
            'total_perfis'       => $total_perfis,
            'documentos_hoje'    => $documentos_hoje,
            'documentos_mes'     => $documentos_mes
        ]);
    }
}