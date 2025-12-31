<?php
namespace app\Controllers;

use app\Core\BaseController;
use app\Core\Log;

class LogController extends BaseController
{
    private Log $log;

    public function __construct()
    {
        parent::__construct();
        $this->log = new Log();
    }

    public function index(): void
    {
        echo $this->twig->render('logs/list.twig', [
            'titulo' => 'Logs do Sistema',
            'logs' => $this->log->listar(200)
        ]);
    }
}