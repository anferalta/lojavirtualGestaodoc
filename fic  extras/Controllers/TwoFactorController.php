<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Conexao;
use App\Core\Usuario;
use App\Core\Sessao;
use App\Core\Helpers;
use OTPHP\TOTP;

class TwoFactorController extends BaseController {

    private Usuario $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new Usuario(Conexao::getInstancia());
    }

    /*
      |--------------------------------------------------------------------------
      | ATIVAR 2FA
      |--------------------------------------------------------------------------
     */

    public function ativar(): void {
        if (!Auth::check()) {
            header("Location: " . url('/login'));
            exit;
        }

        $user = Auth::user();

        // Criar TOTP
        $totp = \OTPHP\TOTP::create();
        $totp->setLabel($user->email);
        $totp->setIssuer('LojaVirtual');

        // Guardar secret na BD
        $secret = $totp->getSecret();
        $this->userModel->update($user->id, [
            'two_factor_secret' => $secret,
            'two_factor_ativo' => 1
        ]);

        // URI para QR Code
        $qrCodeUri = $totp->getProvisioningUri();

        echo $this->twig->render('2fa/ativar.twig', [
            'qrCodeUri' => $qrCodeUri,
            'secret' => $secret
        ]);
    }

    public function confirmar(): void {
        $codigo = $_POST['codigo'] ?? '';
        $secret = $_POST['secret'] ?? '';

        $user = Auth::user();

        if (!$user) {
            Helpers::redirecionar('/login');
        }

        $totp = TOTP::create($secret);

        if (!$totp->verify($codigo)) {
            Auth::logAuth($user->id, $user->email, '2fa_falha_ativacao');
            Sessao::setFlash('Código inválido.', 'danger');
            Helpers::redirecionar('/2fa/ativar');
        }

        // Ativar 2FA
        $this->userModel->ativar2FA($user->id, $secret);

        Auth::logAuth($user->id, $user->email, '2fa_ativado');

        Sessao::setFlash('Autenticação de 2 fatores ativada com sucesso.', 'success');
        Helpers::redirecionar('/painel/seguranca');
    }

    /*
      |--------------------------------------------------------------------------
      | DESATIVAR 2FA
      |--------------------------------------------------------------------------
     */

    public function desativar(): void {
        $user = Auth::user();

        if (!$user) {
            Helpers::redirecionar('/login');
        }

        $this->userModel->desativar2FA($user->id);

        Auth::logAuth($user->id, $user->email, '2fa_desativado');

        Sessao::setFlash('2FA desativado.', 'info');
        Helpers::redirecionar('/painel/seguranca');
    }

    /*
      |--------------------------------------------------------------------------
      | PÁGINA DE SEGURANÇA
      |--------------------------------------------------------------------------
     */

    public function paginaSeguranca(): void {
        $user = Auth::user();

        echo $this->twig->render('painel/seguranca.twig', [
            'user' => $user
        ]);
    }

    /*
      |--------------------------------------------------------------------------
      | VALIDAÇÃO 2FA NO LOGIN
      |--------------------------------------------------------------------------
     */

    public function formValidar(): void {
        echo $this->twig->render('2fa/validar.twig');
    }

    public function validarCodigo(): void {
        $codigo = $_POST['codigo'] ?? '';
        $user = Auth::user();

        if (!$user) {
            Helpers::redirecionar('/login');
        }

        if (!$user->two_factor_ativo) {
            Helpers::redirecionar('/painel');
        }

        $totp = TOTP::create($user->two_factor_secret);

        if (!$totp->verify($codigo)) {
            Auth::logAuth($user->id, $user->email, '2fa_falha');
            Sessao::setFlash('Código inválido.', 'danger');
            Helpers::redirecionar('/2fa/validar');
        }

        $_SESSION['2fa_validado'] = true;

        Auth::logAuth($user->id, $user->email, '2fa_sucesso');

        Helpers::redirecionar('/painel');
    }
}
