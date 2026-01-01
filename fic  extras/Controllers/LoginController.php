<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Conexao;
use App\Core\Usuario;
use App\Core\Sessao;
use App\Core\Helpers;
use DateTime;

class LoginController extends BaseController
{
    private Usuario $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new Usuario(Conexao::getInstancia());
    }

    /**
     * Formulário de login
     */
    public function index(): void
    {
        echo $this->twig->render('auth/login.twig', [
            'form_errors' => [],
            'form_old'    => [],
        ]);
    }

    /**
     * Processar autenticação
     */
    public function autenticar(): void
    {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        $formOld = [
            'email' => $email,
        ];

        if ($email === '' || $senha === '') {
            Sessao::flash('Email e senha são obrigatórios.', 'danger');
            echo $this->twig->render('auth/login.twig', [
                'form_errors' => ['Email e senha são obrigatórios.'],
                'form_old'    => $formOld,
            ]);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        // Não revelar se o email existe ou não
        if (!$user) {
            Sessao::flash('Credenciais inválidas.', 'danger');
            echo $this->twig->render('auth/login.twig', [
                'form_errors' => ['Credenciais inválidas.'],
                'form_old'    => $formOld,
            ]);
            return;
        }

        // Verificar se está bloqueado
        if ($user->bloqueado_ate !== null) {
            $agora = new DateTime();
            $bloqueadoAte = new DateTime($user->bloqueado_ate);

            if ($bloqueadoAte > $agora) {
                $minutos = max(1, (int) ceil(($bloqueadoAte->getTimestamp() - $agora->getTimestamp()) / 60));
                $msg = "Conta temporariamente bloqueada. Tente novamente dentro de {$minutos} minuto(s).";

                Sessao::flash($msg, 'danger');
                echo $this->twig->render('auth/login.twig', [
                    'form_errors' => [$msg],
                    'form_old'    => $formOld,
                ]);
                return;
            }
        }

        // Verificar senha
        if (!password_verify($senha, $user->senha)) {
            $tentativas = (int) $user->tentativas_falhadas + 1;

            $dadosUpdate = [
                'tentativas_falhadas' => $tentativas,
            ];

            // Bloquear após 5 tentativas falhadas
            if ($tentativas >= 5) {
                $bloqueadoAte = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
                $dadosUpdate['bloqueado_ate'] = $bloqueadoAte;
            }

            $this->userModel->update($user->id, $dadosUpdate);

            Sessao::flash('Credenciais inválidas.', 'danger');
            echo $this->twig->render('auth/login.twig', [
                'form_errors' => ['Credenciais inválidas.'],
                'form_old'    => $formOld,
            ]);
            return;
        }

        // Senha correta → limpar tentativas e bloqueio + atualizar último login
        $this->userModel->update($user->id, [
            'tentativas_falhadas' => 0,
            'bloqueado_ate'       => null,
            'ultimo_login'        => date('Y-m-d H:i:s'),
        ]);

        // Se 2FA estiver ativo, pedir código
        if ((int)$user->two_factor_ativo === 1 && !empty($user->two_factor_secret)) {
            Sessao::set('2fa_user_id', $user->id);
            Sessao::set('2fa_pending', true);

            Helpers::redirecionar('/2fa');
            return;
        }

        // Sem 2FA → login normal
        Sessao::login($user);
        Sessao::flash('Bem-vindo de volta, ' . $user->nome . '.', 'success');
        Helpers::redirecionar('/dashboard');
    }

    /**
     * Formulário para código 2FA
     */
    public function twoFactorForm(): void
    {
        if (!Sessao::get('2fa_pending') || !Sessao::get('2fa_user_id')) {
            Helpers::redirecionar('/login');
        }

        echo $this->twig->render('auth/2fa.twig', [
            'form_errors' => [],
        ]);
    }

    /**
     * Validação do código 2FA
     */
    public function twoFactorValidate(): void
    {
        if (!Sessao::get('2fa_pending') || !Sessao::get('2fa_user_id')) {
            Helpers::redirecionar('/login');
        }

        $codigo = trim($_POST['codigo'] ?? '');
        $userId = (int) Sessao::get('2fa_user_id');

        $user = $this->userModel->find($userId);

        if (!$user) {
            Sessao::remove('2fa_pending');
            Sessao::remove('2fa_user_id');
            Sessao::flash('Sessão de autenticação inválida. Tente novamente.', 'danger');
            Helpers::redirecionar('/login');
        }

        if ($codigo === '') {
            Sessao::flash('O código de autenticação é obrigatório.', 'danger');
            echo $this->twig->render('auth/2fa.twig', [
                'form_errors' => ['O código de autenticação é obrigatório.'],
            ]);
            return;
        }

        if (!$this->verificarCodigo2FA($user->two_factor_secret, $codigo)) {
            Sessao::flash('Código de autenticação inválido ou expirado.', 'danger');
            echo $this->twig->render('auth/2fa.twig', [
                'form_errors' => ['Código de autenticação inválido ou expirado.'],
            ]);
            return;
        }

        // 2FA válido → concluir login
        Sessao::login($user);
        Sessao::remove('2fa_pending');
        Sessao::remove('2fa_user_id');

        Sessao::flash('Autenticação concluída com sucesso.', 'success');
        Helpers::redirecionar('/dashboard');
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        Sessao::logout();
        Sessao::flash('Sessão terminada com sucesso.', 'success');
        Helpers::redirecionar('/login');
    }

    /**
     * Verificação do código 2FA (TOTP)
     *
     * Aqui é onde ligas uma biblioteca de TOTP (Google Authenticator).
     * Ex: spomky-labs/otphp ou similar.
     */
    private function verificarCodigo2FA(string $secret, string $codigo): bool
    {
        // PLACEHOLDER:
        // Implementar com uma lib TOTP real (por exemplo, otphp).
        // Exemplo conceptual:
        //
        // $totp = new \OTPHP\TOTP($secret);
        // return $totp->verify($codigo);
        //
        // Por enquanto, isto devolve sempre false para não dar falsa segurança.
        return false;
    }
}