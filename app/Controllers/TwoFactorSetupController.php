<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Helpers;
use App\Core\Sessao;
use OTPHP\TOTP;
use Endroid\QrCode\Builder\Builder;

class TwoFactorSetupController extends BaseController
{
    public function setupForm(): void
    {
        Sessao::start();

        $user = Auth::user();

        // Gerar secret se ainda não existir
        if (empty($user->two_factor_secret)) {
            $secret = TOTP::create()->getSecret();
            Auth::guardarSecret2FA($user->id, $secret);
            $user->two_factor_secret = $secret;
        }

        // Criar TOTP
        $totp = TOTP::create($user->two_factor_secret);
        $totp->setLabel($user->email);
        $totp->setIssuer('LojaVirtual');

        // Gerar QR Code
        $qr = Builder::create()
            ->data($totp->getProvisioningUri())
            ->size(300)
            ->margin(10)
            ->build();

        $qrBase64 = base64_encode($qr->getString());

        $this->view('auth/2fa_setup', [
            'qr' => $qrBase64,
            'secret' => $user->two_factor_secret,
            'csrf' => Sessao::csrf()
        ]);
    }

    public function ativar(): void
    {
        Sessao::start();

        $codigo = $_POST['codigo'] ?? '';

        if (!Auth::validar2fa($codigo)) {
            Sessao::flash('error', 'Código inválido');
            Helpers::redirect('/2fa/setup');
        }

        Auth::ativar2FA(Auth::id());

        Sessao::flash('success', 'Autenticação 2FA ativada com sucesso');
        Helpers::redirect('/perfil');
    }

    public function desativar(): void
    {
        Auth::desativar2FA(Auth::id());
        Sessao::flash('success', '2FA desativado');
        Helpers::redirect('/perfil');
    }
}