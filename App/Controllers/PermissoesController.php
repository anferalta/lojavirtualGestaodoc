<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\PerfilPermissao;

class PermissoesController extends BaseController
{
    public function index(): void
    {
        $perfis = (new Perfil())->all();
        $permissoes = (new Permissao())->all();

        // Obter permissões atribuídas por perfil
        $perfilPermissoes = (new PerfilPermissao())->allGrouped();

        $this->view('permissoes/index', [
            'perfis' => $perfis,
            'permissoes' => $permissoes,
            'perfilPermissoes' => $perfilPermissoes
        ]);
    }

    public function update(): void
    {
        $perfilId = $_POST['perfil_id'] ?? null;
        $permissoes = $_POST['permissoes'] ?? [];

        if (!$perfilId) {
            redirect('/permissoes');
        }

        // Apagar permissões antigas
        (new PerfilPermissao())->deleteWhere('perfil_id', $perfilId);

        // Inserir novas permissões
        foreach ($permissoes as $permId) {
            (new PerfilPermissao())->create([
                'perfil_id' => $perfilId,
                'permissao_id' => $permId
            ]);
        }

        flash('success', 'Permissões atualizadas com sucesso.');
        redirect('/permissoes');
    }
}