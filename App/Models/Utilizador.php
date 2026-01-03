<?php

namespace App\Models;

use App\Core\Model;

class Utilizador extends Model
{
    protected static string $tabela = 'utilizadores';

    protected array $permitidos = [
        'nome',
        'email',
        'senha',
        'nivel',
        'estado',
        'ultimo_login',
        'created_at',
        'updated_at'
    ];

    /**
     * Mapeia os dados vindos da BD para um array limpo
     */
    public function map(array $data): array
    {
        return [
            'id'           => $data['id'] ?? null,
            'nome'         => $data['nome'] ?? null,
            'email'        => $data['email'] ?? null,
            'senha'        => $data['senha'] ?? null,
            'nivel'        => $data['nivel'] ?? null,
            'estado'       => $data['estado'] ?? null,
            'ultimo_login' => $data['ultimo_login'] ?? null,
            'created_at'   => $data['created_at'] ?? null,
            'updated_at'   => $data['updated_at'] ?? null,
        ];
    }

    /**
     * Métodos de domínio (helpers)
     */
    public function isAtivo(): bool
    {
        return (int) ($this->estado ?? 0) === 1;
    }

    public function nivelLabel(): string
    {
        return match ((int) ($this->nivel ?? 0)) {
            1 => 'Básico',
            2 => 'Gestor',
            3 => 'Administrador',
            default => 'Desconhecido'
        };
    }
}