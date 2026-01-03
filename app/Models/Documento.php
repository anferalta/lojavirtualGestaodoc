<?php

namespace App\Models;

use App\Core\Model;

class Documento extends Model
{
    protected static string $tabela = 'documentos';

    protected array $permitidos = [
        'nome',
        'ficheiro',
        'tipo',
        'tamanho',
        'utilizador_id',
        'created_at',
        'updated_at'
    ];

    public function map(array $data): array
    {
        return [
            'id'            => $data['id'] ?? null,
            'nome'          => $data['nome'] ?? null,
            'ficheiro'      => $data['ficheiro'] ?? null,
            'tipo'          => $data['tipo'] ?? null,
            'tamanho'       => $data['tamanho'] ?? null,
            'utilizador_id' => $data['utilizador_id'] ?? null,
            'created_at'    => $data['created_at'] ?? null,
            'updated_at'    => $data['updated_at'] ?? null,
        ];
    }
}