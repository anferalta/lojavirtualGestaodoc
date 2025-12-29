<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use App\Core\Usuario;   // ✔ CORRIGIDO — antes estava "User"
use App\Core\Validator;
use App\Core\Sessao;
use App\Core\Helpers;

class UtilizadoresController extends BaseController {

    private Usuario $userModel;   // ✔ CORRIGIDO

    public function __construct() {
        parent::__construct();
        $this->userModel = new Usuario(Conexao::getInstancia());  // ✔ CORRIGIDO
    }

    /**
     * LISTAGEM COM PAGINAÇÃO
     */
    public function index(): void {
        $pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
        $limite = 15;
        $offset = ($pagina - 1) * $limite;

        $utilizadores = $this->userModel->paginate($limite, $offset);
        $total = $this->userModel->count();
        $totalPaginas = (int) ceil($total / $limite);

        echo $this->twig->render('utilizadores/index.twig', [
            'utilizadores' => $utilizadores,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'flash' => Sessao::flash()
        ]);
    }

    /**
     * MOSTRAR UM UTILIZADOR (SHOW)
     */
    public function show(int $id): void {
        $user = $this->userModel->find($id);

        if (!$user) {
            Sessao::setFlash('Utilizador não encontrado.', 'danger');
            Helpers::redirecionar('/utilizadores');
            return;
        }

        echo $this->twig->render('utilizadores/show.twig', [
            'user' => $user
        ]);
    }

    /**
     * FORMULÁRIO DE CRIAÇÃO
     */
    public function criar(): void {
        echo $this->twig->render('utilizadores/criar.twig', [
        'csrf' => Sessao::csrf(),
        'old' => [],
        'errors' => []
        ]);
    }

    /**
     * PROCESSAR CRIAÇÃO
     */
    public function store(): void {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $senha2 = $_POST['senha2'] ?? '';
        $estado = $_POST['estado'] ?? 'ativo';
        $nivel = $_POST['nivel'] ?? '1';

        $validator = new Validator();

        // Nome
        $validator->required('nome', $nome, 'O nome é obrigatório.');
        $validator->min('nome', $nome, 3, 'O nome deve ter pelo menos 3 caracteres.');

        // Email
        $validator->required('email', $email, 'O email é obrigatório.');
        $validator->email('email', $email, 'O email não é válido.');
        $validator->unique(
                'email',
                $email,
                Conexao::getInstancia(),
                'utilizadores',
                'email',
                'Já existe um utilizador com este email.'
        );

        // Senha
        $validator->required('senha', $senha, 'A senha é obrigatória.');
        $validator->min('senha', $senha, 6, 'A senha deve ter pelo menos 6 caracteres.');
        $validator->match('senha', $senha, $senha2, 'As senhas não coincidem.');

        // Estado
        $validator->in('estado', $estado, ['ativo', 'inativo'], 'Estado inválido.');

        if ($validator->hasErrors()) {
            echo $this->twig->render('utilizadores/criar.twig', [
                'errors' => $validator->getErrors(),
                'old' => [
                    'nome' => $nome,
                    'email' => $email,
                    'estado' => $estado,
                    'nivel' => $nivel,
                ],
                'csrf' => Sessao::csrf()
            ]);
            return;
        }

        // Inserção segura com hash
        $this->userModel->create([
            'nome' => $nome,
            'email' => $email,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'nivel' => $nivel,
            'estado' => $estado
        ]);

        Sessao::setFlash('Utilizador criado com sucesso.', 'success');
        Helpers::redirecionar('/utilizadores');
    }

    /**
     * FORMULÁRIO DE EDIÇÃO
     */
    public function editar(int $id): void {
        $user = $this->userModel->find($id);

        if (!$user) {
            Sessao::setFlash('Utilizador não encontrado.', 'danger');
            Helpers::redirecionar('/utilizadores');
            return;
        }

        echo $this->twig->render('utilizadores/editar.twig', [
            'user' => $user,
            'csrf' => Sessao::csrf()
        ]);
    }

    /**
     * PROCESSAR UPDATE
     */
    public function update(int $id): void {
        $user = $this->userModel->find($id);

        if (!$user) {
            Sessao::setFlash('Utilizador não encontrado.', 'danger');
            Helpers::redirecionar('/utilizadores');
            return;
        }

        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $estado = $_POST['estado'] ?? 'ativo';
        $nivel = $_POST['nivel'] ?? '1';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $novaSenha2 = $_POST['nova_senha2'] ?? '';

        $validator = new Validator();

        // Nome
        $validator->required('nome', $nome, 'O nome é obrigatório.');
        $validator->min('nome', $nome, 3, 'O nome deve ter pelo menos 3 caracteres.');

        // Email
        $validator->required('email', $email, 'O email é obrigatório.');
        $validator->email('email', $email, 'O email não é válido.');

        if ($email !== $user->email) {
            $validator->unique(
                    'email',
                    $email,
                    Conexao::getInstancia(),
                    'utilizadores',
                    'email',
                    'Já existe um utilizador com este email.'
            );
        }

        // Estado
        $validator->in('estado', $estado, ['ativo', 'inativo'], 'Estado inválido.');

        // Senha opcional
        if ($novaSenha !== '') {
            $validator->min('nova_senha', $novaSenha, 6, 'A nova senha deve ter pelo menos 6 caracteres.');
            $validator->match('nova_senha', $novaSenha, $novaSenha2, 'As senhas não coincidem.');
        }

        if ($validator->hasErrors()) {
            echo $this->twig->render('utilizadores/editar.twig', [
                'user' => $user,
                'errors' => $validator->getErrors(),
                'old' => [
                    'nome' => $nome,
                    'email' => $email,
                    'estado' => $estado,
                    'nivel' => $nivel,
                ],
                'csrf' => Sessao::csrf()
            ]);
            return;
        }

        // Dados base
        $dados = [
            'nome' => $nome,
            'email' => $email,
            'nivel' => $nivel,
            'estado' => $estado
        ];

        // Se houver nova senha
        if ($novaSenha !== '') {
            $dados['senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $dados);

        Sessao::setFlash('Utilizador atualizado com sucesso.', 'success');
        Helpers::redirecionar('/utilizadores');
    }

    /**
     * ELIMINAR UTILIZADOR
     */
    public function delete(int $id): void {
        $user = $this->userModel->find($id);

        if (!$user) {
            Sessao::setFlash('Utilizador não encontrado.', 'danger');
            Helpers::redirecionar('/utilizadores');
            return;
        }

        $this->userModel->delete($id);

        Sessao::setFlash('Utilizador eliminado com sucesso.', 'success');
        Helpers::redirecionar('/utilizadores');
    }
}
