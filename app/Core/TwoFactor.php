<?php

namespace App\Core;

use OTPHP\TOTP;

class TwoFactor
{
    /**
     * Gerar secret para o utilizador
     */
    public static function generateSecret(): string
    {
        return TOTP::create()->getSecret();
    }

    /**
     * Gerar QR Code URI (Google Authenticator)
     */
    public static function getQRCode(string $email, string $secret): string
    {
        $totp = TOTP::create($secret);
        $totp->setLabel($email);

        return $totp->getQrCodeUri();
    }

    /**
     * Validar código 2FA
     */
    public static function verify(string $secret, string $code): bool
    {
        $totp = TOTP::create($secret);
        return $totp->verify($code);
    }
}