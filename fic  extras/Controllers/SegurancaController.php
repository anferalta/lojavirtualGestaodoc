<?php
namespace app\Controllers;

use app\Core\BaseController;
use app\Core\Auth;
use app\Core\Conexao;

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