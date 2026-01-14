<?php

namespace App\Models;

use App\Core\Model;

class Perfil extends Model
{
    protected static string $tabela = 'perfis';

    public ?int $id = null;
    public ?string $nome = null;
    public ?string $slug = null;
    public ?string $descricao = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    protected array $permitidos = [
        'nome',
        'slug',
        'descricao',
        'created_at',
        'updated_at'
    ];
}