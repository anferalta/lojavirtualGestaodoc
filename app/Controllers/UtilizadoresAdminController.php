<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Utilizador;
use App\Models\Perfil;
use App\Models\UtilizadorPerfil;

class UtilizadoresAdminController extends BaseController
{
    public function index(): void
    {
        $utilizadores = (new Utilizador())->allWithPerfis();

        $this->view('utilizadores/index', [
            'utilizadores' => $utilizadores
        ]);
    }

    public function create(): void
    {
        $perfis = (new Perfil())->all();

        $this->view('utilizadores/create', [
            'perfis' => $perfis
        ]);
    }

    public function store(): void
    {
        $dados = [
            'nome'  => trim($_POST['nome'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'estado' => 1
        ];

        $perfilId = $_POST['perfil_id'] ?? null;

        if ($dados['nome'] === '' || $dados['email'] === '') {
            flash('error', 'Nome e email são obrigatórios.');
            redirect('/admin/utilizadores/criar');
        }

        $utilizadorModel = new Utilizador();
       