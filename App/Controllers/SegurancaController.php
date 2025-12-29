<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Conexao;

class SegurancaController extends BaseController
{
    public function index(): void
    {
        $db = Conexao::getInstancia();
        $user = Auth::user();

        $stm = $db->prepare("SELECT * FROM logs_auth WHERE user_id = :id ORDER BY id DESC LIMIT 20");
        $stm->execute(['id' => $user->id]);
        $logs = $stm->fetchAll(\PDO::FETCH_OBJ);

        echo $this->twig->render('painel/seguranca-dashboard.twig', [
            'user' => $user,
            'logs' => $logs
        ]);
    }
}