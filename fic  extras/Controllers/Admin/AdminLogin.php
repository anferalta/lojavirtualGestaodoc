<?php

namespace app\Controllers\Admin;

use app\Core\Controller;
use app\Core\Helpers;
use app\Model\UsuarioModel;

class AdminLoginController extends Controller
{
    public function __construct()
    {
        parent::__construct('app/Views');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        if (isset($_SESSION['usuario']) && $_SESSION['usuario']->level == 3) {
            Helpers::redirecionar('/home');
        } else {
            Helpers::redirecionar('/login');
        }
    }

    public function login(): void
    {
        // Se já está logado como admin
        if (isset($_SESSION['usuario']) && $_SESSION['usuario']->level == 3) {
            Helpers::redirecionar('/home');
        }

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if ($dados) {
            if (in_array('', $dados)) {
                $_SESSION['flash'][] = ['tipo' => 'danger', 'conteudo' => 'Todos os campos são obrigatórios!'];
            } else {
                $usuario = (new UsuarioModel())->login($dados, 3);

                if ($usuario) {
                    $_SESSION['usuario'] = $usuario;
                    Helpers::redirecionar('/home');
                } else {
                    $_SESSION['flash'][] = ['tipo' => 'danger', 'conteudo' => 'Credenciais inválidas!'];
                }
            }
        }

        $this->renderView('Site/login.twig', [
            'title' => 'Login Administrador'
        ]);
    }
}