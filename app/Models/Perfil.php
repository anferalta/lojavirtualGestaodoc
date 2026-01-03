<?php

namespace App\Models;

use App\Core\Model;

class Perfil extends Model
{
    protected static string $tabela = 'perfis';

    protected array $permitidos = [
        'nome',
        'descricao',
        'created_at',
        'updated_at'
    ];

    public function map(array $data): array
    {
        return [
            'id'         => $data['id'] ?? null,
            'nome'       => $data['nome'] ?? null,
            'descricao'  => $data['descricao'] ?? null,
            'created_at' => $data['created_at'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
        ];
    }
}