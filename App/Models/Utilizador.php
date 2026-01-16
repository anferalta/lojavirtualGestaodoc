<?php

namespace App\Models;

use App\Core\Model;

class Utilizador extends Model
{
    protected static string $tabela = 'utilizadores';

    // === COLUNAS DA TABELA ===
    public ?int $id = null;
    public ?string $nome = null;
    public ?string $email = null;
    public ?string $senha = null;

    public ?string $two_factor_secret = null;
    public ?int $two_factor_ativo = null;

    public ?int $estado = null;
    public ?int $nivel = null;

    public ?string $avatar = null;
    public ?int $perfil_id = null;

    public ?string $ultimo_login = null;
    public ?int $tentativas_falhadas = null;
    public ?string $bloqueado_ate = null;

    public ?string $reset_token = null;
    public ?string $reset_token_expira = null;

    public ?string $criado_em = null;
    public ?string $atualizado_em = null;

    // === CAMPOS PERMITIDOS PARA INSERT/UPDATE ===
    protected array $permitidos = [
        'nome',
        'email',
        'senha',
        'two_factor_secret',
        'two_factor_ativo',
        'estado',
        'nivel',
        'avatar',
        'perfil_id',
        'ultimo_login',
        'tentativas_falhadas',
        'bloqueado_ate',
        'reset_token',
        'reset_token_expira'
    ];

    // === MÉTODOS DE NEGÓCIO ===

    public function create(array $dados): ?int
    {
        $dados = array_intersect_key($dados, array_flip($this->permitidos));

        if (!empty($dados['senha'])) {
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }

        return $this->insert($dados);
    }

    public function updateUser(int $id, array $dados): bool
    {
        $dados = array_intersect_key($dados, array_flip($this->permitidos));

        if (!empty($dados['senha'])) {
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        } else {
            unset($dados['senha']);
        }

        return $this->update($dados, "id = :id", [':id' => $id]);
    }

    public function deleteUser(int $id): bool
    {
        return $this->delete($id);
    }

    // === HELPERS ===

    public function avatarUrl(): string
    {
        return $this->avatar
            ? '/uploads/avatars/' . $this->avatar
            : '/assets/img/avatar-default.png';
    }

    public function estadoBadge(): string
    {
        return $this->estado == 1
            ? '<span class="badge bg-success">Ativo</span>'
            : '<span class="badge bg-secondary">Inativo</span>';
    }

    public function perfil()
    {
        if (empty($this->perfil_id) || !is_numeric($this->perfil_id)) {
            return null;
        }

        static $cache = [];

        if (!isset($cache[$this->perfil_id])) {
            $cache[$this->perfil_id] = (new Perfil())->find((int) $this->perfil_id);
        }

        return $cache[$this->perfil_id];
    }
}