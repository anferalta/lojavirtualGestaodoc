<?php

namespace App\Models;

use App\Core\Model;

class Utilizador extends Model {

    protected static string $tabela = 'utilizadores';
    // PROPRIEDADES
    public ?int $id = null;
    public ?string $nome = null;
    public ?string $email = null;
    public ?string $senha = null;
    public ?int $nivel = null;
    public ?int $estado = null;
    public ?string $ultimo_login = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public ?string $two_factor_secret = null;
    public ?int $two_factor_ativo = null;
    public ?string $avatar = null;
    public ?int $perfil_id = null;
    public ?int $tentativas_falhadas = null;
    public ?string $bloqueado_ate = null;
    public ?string $reset_token = null;
    public ?string $reset_token_expira = null;
    public ?string $criado_em = null;
    public ?string $atualizado_em = null;
    protected array $permitidos = [
        'nome',
        'email',
        'senha',
        'nivel',
        'estado',
        'ultimo_login',
        'created_at',
        'updated_at',
        'two_factor_secret',
        'two_factor_ativo',
        'avatar',
        'perfil_id',
        'tentativas_falhadas',
        'bloqueado_ate',
        'reset_token',
        'reset_token_expira',
        'criado_em',
        'atualizado_em'
    ];

    /* ============================================================
     *  MAP
     * ============================================================ */

    public function map(array $data): array {
        return [
            'id' => $data['id'] ?? null,
            'nome' => $data['nome'] ?? null,
            'email' => $data['email'] ?? null,
            'senha' => $data['senha'] ?? null,
            'nivel' => $data['nivel'] ?? null,
            'estado' => $data['estado'] ?? null,
            'ultimo_login' => $data['ultimo_login'] ?? null,
            'created_at' => $data['created_at'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
            'two_factor_secret' => $data['two_factor_secret'] ?? null,
            'two_factor_ativo' => $data['two_factor_ativo'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'perfil_id' => $data['perfil_id'] ?? null,
            'tentativas_falhadas' => $data['tentativas_falhadas'] ?? null,
            'bloqueado_ate' => $data['bloqueado_ate'] ?? null,
            'reset_token' => $data['reset_token'] ?? null,
            'reset_token_expira' => $data['reset_token_expira'] ?? null,
            'criado_em' => $data['criado_em'] ?? null,
            'atualizado_em' => $data['atualizado_em'] ?? null,
        ];
    }

    /* ============================================================
     *  MÉTODOS DE NEGÓCIO
     * ============================================================ */

    public function isAtivo(): bool {
        return (int) ($this->estado ?? 0) === 1;
    }

    public function nivelLabel(): string {
        return match ((int) ($this->nivel ?? 0)) {
            1 => 'Básico',
            2 => 'Gestor',
            3 => 'Administrador',
            default => 'Desconhecido'
        };
    }

    /* ============================================================
     *  CRUD NORMALIZADO
     * ============================================================ */

    /** CREATE */
    public function create(array $dados): ?int {
        // filtrar apenas os campos permitidos
        $dados = array_intersect_key($dados, array_flip($this->permitidos));

        // hash da senha
        if (!empty($dados['senha'])) {
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }

        return $this->insert($dados);
    }

    /** UPDATE */
    public function updateUser(int $id, array $dados): bool {
        $dados = array_intersect_key($dados, array_flip($this->permitidos));

        if (!empty($dados['senha'])) {
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        } else {
            unset($dados['senha']);
        }

        return $this->update($dados, "id = :id", [':id' => $id]);
    }

    /** DELETE */
    public function deleteUser(int $id): bool {
        return $this->delete($id);
    }
}
