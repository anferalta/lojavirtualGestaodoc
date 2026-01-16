<?php

namespace App\Models;

use App\Core\Model;

class Permissao extends Model
{
    protected static string $tabela = 'permissoes';

    public int $id;
    public string $chave;
    public string $descricao;
}