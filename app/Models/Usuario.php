<?php

namespace App\Models;

class Usuario
{
    // Colunas obrigatórias
    public int $id;
    public string $nome;
    public string $email;
    public string $senha;

    // 2FA
    public ?string $two_factor_secret = null;
    public int $two_factor_ativo = 0;

    // Estado e nível
    public int $estado = 1;
    public int $nivel = 1;

    // Avatar
    public ?string $avatar = null;

    // Perfil (ACL)
    public ?int $perfil_id = null;

    // Segurança
    public ?string $ultimo_login = null;
    public int $tentativas_falhadas = 0;
    public ?string $bloqueado_ate = null;

    // Recuperação de password
    public ?string $reset_token = null;
    public ?string $reset_token_expira = null;

    // Auditoria
    public string $criado_em;
    public string $atualizado_em;
}