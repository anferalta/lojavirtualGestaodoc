<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use PDO;

class AuditoriaController extends BaseController
{
    public function index(): void
    {
        $db = Conexao::getInstancia();

        $sql = "SELECT a.*, u.nome AS utilizador_nome
                FROM auditoria a
                LEFT JOIN utilizadores u ON u.id = a.utilizador_id
                ORDER BY a.criado_em DESC
                LIMIT 200";

        $registos = $db->query($sql)->fetchAll(PDO::FETCH_OBJ);

        echo $this->twig->render('auditoria/index.twig', [
            'registos' => $registos,
        ]);
    }
}