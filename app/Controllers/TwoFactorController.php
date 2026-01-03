<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Sessao;
use App\Core\TwoFactor;

class TwoFactorController extends BaseController
{
    /**
     * Página onde o utilizador ativa o 2FA
     */
    public function ativar(): void
    {
        $user = Auth::user();

        // Gerar secret e QR
        $secret = TwoFactor::generateSecret();
        $qrCode = TwoFactor::getQRCode($user->email, $secret);

        // Guardar secret temporariamente na sessão
        $_SESSION['2fa_secret_temp'] = $secret;

        $this->view('2fa/ativar', [
            'qrCode' => $qrCode,
            'secret' => $secret
        ]);
    }

    /**
     * Confirmar ativação do 2FA
     */
    public function confirmar(): void
    {
        $user = Auth::user();
        $codigo = trim($_POST['codigo'] ?? '');

        if (!isset($_SESSION['2fa_secret_temp'])) {
            Sessao::flash('Erro interno. Tente novamente.', 'error');
            $this->redirect('/2fa/ativar');
        }

        $secret = $_SESSION['2fa_secret_temp'];

        if (!TwoFactor::verify($secret, $codigo)) {
            Sessao::flash('Código inválido.', 'error');
            $this->redirect('/2fa/ativar');
        }

        // Guardar secret definitivo no utilizador
        $user->two_factor_secret = $secret;
        $user->two_factor_ativo = 1;
        $user->save();

        unset($_SESSION['2fa_secret_temp']);

        Sessao::flash('2FA ativado com sucesso!', 'success');
        $this->redirect('/painel/seguranca');
    }

    /**
     * Formulário para validar o código 2FA após login
     */
    public function formValidar(): void
    {
        $this->view('2fa/validar');
    }

    /**
     * Validar o código 2FA após login
     */
    public function validarCodigo(): void
    {
        $user = Auth::user();
        $codigo = trim($_POST['codigo'] ?? '');

        if (!$user->two_factor_ativo) {
            Sessao::flash('2FA não está ativo.', 'warning');
            $this->redirect('/dashboard');
        }

        if (!TwoFactor::verify($user->two_factor_secret, $codigo)) {
            Sessao::flash('Código inválido.', 'error');
            $this->redirect('/2fa/validar');
        }

        // Marcar como validado
        $_SESSION['2fa_validado'] = true;

        Sessao::flash('Autenticação verificada com sucesso!', 'success');
        $this->redirect('/dashboard');
    }

    /**
     * Desativar 2FA
     */
    public function desativar(): void
    {
        $user = Auth::user();

        $user->two_factor_ativo = 0;
        $user->two_factor_secret = null;
        $user->save();

        unset($_SESSION['2fa_validado']);

        Sessao::flash('2FA desativado com sucesso.', 'info');
        $this->redirect('/painel/seguranca');
    }

    /**
     * Página de segurança do painel
     */
    public function paginaSeguranca(): void
    {
        $user = Auth::user();

        $this->view('painel/seguranca', [
            'user' => $user
        ]);
    }
}