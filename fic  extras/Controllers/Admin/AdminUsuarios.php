<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Helpers;
use App\Model\UsuarioModel;

class AdminUsuariosController extends Controller
{
    public function __construct()
    {
        parent::__construct('app/Views');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function listar(): void
    {
        $usuarioModel = new UsuarioModel();

        $dados = [
            'usuarios' => $usuarioModel->busca()->ordem('level DESC, status ASC')->resultado(true),
            'total' => [
                'usuarios'       => $usuarioModel->busca('level != 3')->total(),
                'usuariosAtivo'  => $usuarioModel->busca('status = 1 AND level != 3')->total(),
                'usuariosInativo'=> $usuarioModel->busca('status = 0 AND level != 3')->total(),
                'admin'          => $usuarioModel->busca('level = 3')->total(),
                'adminAtivo'     => $usuarioModel->busca('status = 1 AND level = 3')->total(),
                'adminInativo'   => $usuarioModel->busca('status = 0 AND level = 3')->total(),
            ]
        ];

        $this->renderView('usuarios/listar.twig', $dados);
    }

    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if ($dados && $this->validarDados($dados, true)) {
            $usuario = new UsuarioModel();
            $usuario->nome = $dados['nome'];
            $usuario->email = $dados['email'];
            $usuario->senha = Helpers::gerarSenha($dados['senha']);
            $usuario->level = $dados['level'];
            $usuario->status = $dados['status'];
            $usuario->cadastrado_em = date('Y-m-d H:i:s');

            if ($usuario->salvar()) {
                $_SESSION['flash'][] = ['tipo' => 'success', 'conteudo' => 'Usuário cadastrado com sucesso'];
                Helpers::redirecionar('/admin/usuarios/listar');
            } else {
                $_SESSION['flash'][] = ['tipo' => 'danger', 'conteudo' => $usuario->erro()];
            }
        }

        $this->renderView('usuarios/formulario.twig', [
            'usuario' => $dados ?? null
        ]);
    }

    public function editar(int $id): void
    {
        $usuario = (new UsuarioModel())->buscaPorId($id);

        if (!$usuario) {
            $_SESSION['flash'][] = ['tipo' => 'warning', 'conteudo' => 'Usuário não encontrado'];
            Helpers::redirecionar('/admin/usuarios/listar');
        }

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if ($dados && $this->validarDados($dados, false)) {
            $usuario->nome = $dados['nome'];
            $usuario->email = $dados['email'];
            $usuario->level = $dados['level'];
            $usuario->status = $dados['status'];
            $usuario->atualizado_em = date('Y-m-d H:i:s');

            if (!empty($dados['senha'])) {
                $usuario->senha = Helpers::gerarSenha($dados['senha']);
            }

            if ($usuario->salvar()) {
                $_SESSION['flash'][] = ['tipo' => 'success', 'conteudo' => 'Usuário atualizado com sucesso'];
                Helpers::redirecionar('/admin/usuarios/listar');
            } else {
                $_SESSION['flash'][] = ['tipo' => 'danger', 'conteudo' => $usuario->erro()];
            }
        }

        $this->renderView('usuarios/formulario.twig', [
            'usuario' => $usuario
        ]);
    }

    public function deletar(int $id): void
    {
        $usuario = (new UsuarioModel())->buscaPorId($id);

        if (!$usuario) {
            $_SESSION['flash'][] = ['tipo' => 'warning', 'conteudo' => 'O usuário não existe!'];
            Helpers::redirecionar('/admin/usuarios/listar');
        }

        if (isset($_SESSION['usuario']) && $usuario->id === $_SESSION['usuario']->id) {
            $_SESSION['flash'][] = ['tipo' => 'warning', 'conteudo' => 'Você não pode deletar seu próprio usuário!'];
            Helpers::redirecionar('/admin/usuarios/listar');
        } elseif ($usuario->deletar()) {
            $_SESSION['flash'][] = ['tipo' => 'success', 'conteudo' => 'Usuário deletado com sucesso!'];
        } else {
            $_SESSION['flash'][] = ['tipo' => 'danger', 'conteudo' => $usuario->erro()];
        }

        Helpers::redirecionar('/admin/usuarios/listar');
    }

    public function validarDados(array $dados, bool $senhaObrigatoria = false): bool
    {
        if (empty($dados['nome'])) {
            $_SESSION['flash'][] = ['tipo' => 'warning', 'conteudo' => 'Informe o nome do usuário'];
            return false;
        }

        if (empty($dados['email']) || !Helpers::validarEmail($dados['email'])) {
            $_SESSION['flash'][] = ['tipo' => 'warning', 'conteudo' => 'Informe um e-mail válido'];
            return false;
        }

        if (!empty($dados['senha']) || $senhaObrigatoria) {
            if (empty($dados['senha']) || !Helpers::validarSenha($dados['senha'])) {
                $_SESSION['flash'][] = ['tipo' => 'warning', 'conteudo' => 'A senha deve ter entre 6 e 50 caracteres'];
                return false;
            }
        }

        return true;
    }
}