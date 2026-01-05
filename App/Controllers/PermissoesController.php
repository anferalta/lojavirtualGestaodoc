<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\PerfilPermissao;

class PermissoesController extends BaseController {

    public function index(): void {
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

    public function update(): void {
        $perfilId = $_POST['perfil_id'] ?? null;
        $permissoes = $_POST['permissoes'] ?? [];

        if (!$perfilId) {
            redirect('/permissoes');
        }

        $perfilPermissaoModel = new PerfilPermissao();

        // Permissões antigas
        $antes = $perfilPermissaoModel->getPermissoesIdsByPerfil($perfilId);

        // Apagar permissões antigas
        $perfilPermissaoModel->deleteWhere('perfil_id', $perfilId);

        // Inserir novas permissões
        foreach ($permissoes as $permId) {
            $perfilPermissaoModel->create([
                'perfil_id' => $perfilId,
                'permissao_id' => $permId
            ]);
        }

        // Permissões novas
        $depois = $perfilPermissaoModel->getPermissoesIdsByPerfil($perfilId);

        // Registar auditoria
        $this->registarAuditoriaPermissoes($perfilId, $antes, $depois);

        flash('success', 'Permissões atualizadas com sucesso.');
        redirect('/permissoes');
    }

    private function registarAuditoriaPermissoes(int $perfilId, array $antes, array $depois): void {
        $utilizadorId = auth()->id(); // ajusta para a tua função auth

        $alteracoes = [
            'antes' => $antes,
            'depois' => $depois
        ];

        (new \App\Models\AuditoriaPermissoes())->create([
            'utilizador_id' => $utilizadorId,
            'perfil_id' => $perfilId,
            'alteracoes' => json_encode($alteracoes, JSON_UNESCAPED_UNICODE)
        ]);
    }
}
