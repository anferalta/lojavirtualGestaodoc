<?php

use App\Models\Permissao;

class PermissoesSeeder
{
    public function run(): void
    {
        $permissoes = [ /* lista acima */ ];

        $model = new Permissao();

        foreach ($permissoes as $nome) {
            if (!$model->where('nome', '=', $nome)->first()) {
                $model->create(['nome' => $nome]);
            }
        }
    }
}