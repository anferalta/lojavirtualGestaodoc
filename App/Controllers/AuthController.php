<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Sessao;

class AuthController extends BaseController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login');
    }

    public function login(): void
    {
        // Validar CSRF
        $token = $_POST['_csrf'] ?? '';
        if (!Sessao::validarCsrf($token)) {
            Sessao::flash('Sessão expirada. Por favor tente novamente.', 'error');
            $this->redirect('/login');
        }

        // Normalizar inputs
        $email = strtolower(trim($_POST['email'] ?? ''));
        $senha = trim($_POST['senha'] ?? '');

        if ($email === '' || $senha === '') {
            Sessao::flash('Preencha todos os campos.', 'warning');
            $this->redirect('/login');
        }

        // Autenticação
        if (!Auth::attempt($email, $senha)) {
            Sessao::flash('Credenciais inválidas.', 'error');
            $this->redirect('/login');
        }

        // Obter utilizador autenticado
        $user = Auth::user();

        // Se o 2FA estiver ativo e ainda não validado
        if ($user->two_factor_ativo == 1 && !isset($_SESSION['2fa_validado'])) {
            Sessao::flash('Confirme o código de autenticação.', 'info');
            $this->redirect('/2fa/validar');
        }

        // Sucesso normal
        Sessao::flash('Bem-vindo de volta!', 'success');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        unset($_SESSION['2fa_validado']);
        Sessao::flash('Sessão terminada com sucesso.', 'info');
        $this->redirect('/login');
    }
}