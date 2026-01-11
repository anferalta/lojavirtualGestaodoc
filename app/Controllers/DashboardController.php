<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\ACL;
use App\Models\Utilizador;
use App\Models\Documento;
use App\Models\Perfil;

class DashboardController extends BaseController
{
    public function index()
    {
        // Obter utilizador autenticado
        $user = Auth::user();

        // Segurança: se não existir utilizador, redirecionar para login
        if (!$user) {
            return redirecionar('/login');
        }

        // Estatísticas
        $totalUtilizadores = Utilizador::count();
        $totalDocumentos   = Documento::count();
        $totalPerfis       = Perfil::count();

        // Documentos criados hoje
        $documentosHoje = Documento::countWhere("DATE(criado_em) = CURDATE()");

        // Documentos do mês atual
        $documentosMes = Documento::countWhere("
            MONTH(criado_em) = MONTH(NOW()) 
            AND YEAR(criado_em) = YEAR(NOW())
        ");

        $this->view('painel/index', [
            'usuario_nome'       => $user->nome,
            'user'               => $user,
            'acl'                => new ACL($user->perfil_id),

            // Estatísticas
            'total_utilizadores' => $totalUtilizadores,
            'total_documentos'   => $totalDocumentos,
            'total_perfis'       => $totalPerfis,
            'documentos_hoje'    => $documentosHoje,
            'documentos_mes'     => $documentosMes
        ]);
    }
}