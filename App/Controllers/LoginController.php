<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use App\Core\Sessao;
use App\Core\Helpers;
use App\Core\Validator;
use App\Core\Auth;
use App\Core\Mailer;

class LoginController extends BaseController {

    private $userModel;
    private $db;

    public function __construct() {
        parent::__construct();
        $this->db = Conexao::getInstancia();
        $this->userModel = new \App\Core\Usuario($this->db);
    }

    public function index(): void {
        echo $this->twig->render('login.twig');
    }

    /*
      |--------------------------------------------------------------------------
      | LOGIN
      |--------------------------------------------------------------------------
     */

    public function autenticar(): void {
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        // Validação
        $validator = new Validator();
        $validator->required('email', $email, 'O email é obrigatório.');
        $validator->required('senha', $senha, 'A senha é obrigatória.');

        if ($validator->hasErrors()) {
            Sessao::setFlash('Credenciais inválidas.', 'danger');
            Helpers::redirecionar('/login');
        }

        $user = $this->userModel->findByEmail($email);

        // Email não existe
        if (!$user) {
            Auth::registarFalhaLogin($email);
            Sessao::setFlash('Credenciais inválidas.', 'danger');
            Helpers::redirecionar('/login');
        }

        // Conta bloqueada
        if (Auth::estaBloqueado($user)) {
            Sessao::setFlash('Conta temporariamente bloqueada devido a tentativas falhadas.', 'danger');
            Helpers::redirecionar('/login');
        }

        // Senha incorreta
        if (!password_verify($senha, $user->senha)) {
            Auth::registarFalhaLogin($email);
            Sessao::setFlash('Credenciais inválidas.', 'danger');
            Helpers::redirecionar('/login');
        }



        // Login OK → limpar tentativas
        Auth::limparTentativas($user->id);

        // Guardar sessão base
        Auth::login($user);

        // Se tiver 2FA ativo → pedir código
        if ($user->two_factor_ativo == 1) {
            unset($_SESSION['2fa_validado']);
            Helpers::redirecionar('/2fa/validar');
        }

        // Atualizar último login
        $this->userModel->atualizarUltimoLogin($user->id);

        Auth::logAuth($user->id, $user->email, 'login_sucesso');

        Helpers::redirecionar('/painel');
    }

    /*
      |--------------------------------------------------------------------------
      | RECUPERAÇÃO DE SENHA
      |--------------------------------------------------------------------------
     */

    public function recuperar(): void {
        echo $this->twig->render('recuperar.twig');
    }

    public function show($id) {
        $user = $this->userModel->find($id);

        if (!$user) {
            Sessao::setFlash('Utilizador não encontrado.', 'danger');
            Helpers::redirecionar('/utilizadores');
        }

        echo $this->twig->render('utilizadores/show.twig', [
            'user' => $user
        ]);
    }

    public function enviarRecuperacao(): void {
        $email = trim($_POST['email'] ?? '');

        $validator = new Validator();
        $validator->required('email', $email, 'O email é obrigatório.');

        if ($validator->hasErrors()) {
            Sessao::setFlash('Email inválido.', 'danger');
            Helpers::redirecionar('/recuperar');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            Sessao::setFlash('Se o email existir, receberá instruções.', 'info');
            Auth::logAuth(null, $email, 'reset_pedido_inexistente');
            Helpers::redirecionar('/recuperar');
        }

        // Limpar tokens antigos
        $this->db->prepare("DELETE FROM password_resets WHERE email = :email")
                ->execute(['email' => $email]);

        $token = bin2hex(random_bytes(32));
        $expira = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');

        $sql = "INSERT INTO password_resets (email, token, expiracao)
                VALUES (:email, :token, :exp)";
        $stm = $this->db->prepare($sql);
        $stm->execute([
            'email' => $email,
            'token' => $token,
            'exp' => $expira
        ]);

        $link = Helpers::url('/redefinir?token=' . urlencode($token));

        Mailer::enviar(
                $email,
                'Recuperação de senha',
                $this->twig->render('emails/recuperar_senha.twig', [
                    'nome' => $user->nome ?? $email,
                    'link' => $link
                ])
        );

        Auth::logAuth($user->id, $email, 'reset_pedido');

        Sessao::setFlash('Se o email existir, receberá instruções.', 'info');
        Helpers::redirecionar('/login');
    }

    public function formRedefinir(): void {
        $token = $_GET['token'] ?? '';

        if (!$token) {
            Sessao::setFlash('Token inválido.', 'danger');
            Helpers::redirecionar('/login');
        }

        echo $this->twig->render('redefinir.twig', [
            'token' => $token
        ]);
    }

    public function redefinirSenha(): void {
        $token = $_POST['token'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirm = $_POST['senha_confirm'] ?? '';

        $validator = new Validator();
        $validator->required('senha', $senha, 'A senha é obrigatória.');
        $validator->required('senha_confirm', $confirm, 'A confirmação é obrigatória.');

        if ($senha !== $confirm) {
            $validator->addError('senha_confirm', 'As senhas não coincidem.');
        }

        if ($validator->hasErrors()) {
            Sessao::setFlash('Verifique os dados submetidos.', 'danger');
            Helpers::redirecionar('/redefinir?token=' . urlencode($token));
        }

        $sql = "SELECT * FROM password_resets
                WHERE token = :token AND usado = 0 AND expiracao > NOW()";
        $stm = $this->db->prepare($sql);
        $stm->execute(['token' => $token]);
        $reset = $stm->fetch();

        if (!$reset) {
            Sessao::setFlash('Token inválido ou expirado.', 'danger');
            Helpers::redirecionar('/login');
        }

        $user = $this->userModel->findByEmail($reset['email']);

        if (!$user) {
            Sessao::setFlash('Utilizador não encontrado.', 'danger');
            Helpers::redirecionar('/login');
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $this->userModel->updatePasswordByEmail($reset['email'], $hash);

        $this->db->prepare("UPDATE password_resets SET usado = 1 WHERE id = :id")
                ->execute(['id' => $reset['id']]);

        Auth::logAuth($user->id, $user->email, 'reset_concluido');

        Sessao::setFlash('Senha atualizada com sucesso. Pode iniciar sessão.', 'success');
        Helpers::redirecionar('/login');
    }
}
