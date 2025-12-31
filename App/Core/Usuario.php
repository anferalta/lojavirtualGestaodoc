<?php

namespace app\Core;

use PDO;
use PDOException;

class Usuario {

    private PDO $db;
    private string $table = 'utilizadores';

    public function __construct(PDO $conexao) {
        $this->db = $conexao;
    }

    /**
     * Encontrar um utilizador pelo ID
     */
    public function find(int $id): ?object {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_OBJ);
        return $user ?: null;
    }

    /**
     * Listagem com paginação
     */
    public function paginate(int $limit, int $offset): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Contar total de utilizadores
     */
    public function count(): int {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_OBJ);

        return (int) $row->total;
    }

    /**
     * Criar utilizador
     */
    public function create(array $dados): bool {
        $sql = "INSERT INTO {$this->table} 
                (nome, email, senha, nivel, estado) 
                VALUES (:nome, :email, :senha, :nivel, :estado)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
                    ':nome' => $dados['nome'],
                    ':email' => $dados['email'],
                    ':senha' => $dados['senha'],
                    ':nivel' => $dados['nivel'],
                    ':estado' => $dados['estado']
        ]);
    }

    /**
     * Atualizar utilizador
     */
    public function update(int $id, array $dados): bool {
        $campos = [];
        $params = [];

        foreach ($dados as $campo => $valor) {
            $campos[] = "{$campo} = :{$campo}";
            $params[":{$campo}"] = $valor;
        }

        $params[':id'] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Eliminar utilizador
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Encontrar por email (útil para login)
     */
    public function findByEmail(string $email): ?object {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_OBJ);
        return $user ?: null;
    }

    public function isAtivo(): bool {
        return $this->estado == 1;
    }

    public function nivelLabel(): string {
        return match ($this->nivel) {
            1 => 'Básico',
            2 => 'Gestor',
            3 => 'Administrador',
            default => 'Desconhecido'
        };
    }

    public function temPermissao(string $perm): bool {
        $acl = new Permission($this->db);
        return $acl->userHas($this->id, $perm);
    }

    public function ultimoLogin(): ?string {
        return Sessao::get('ultimo_login');
    }

    public function perfil(): ?object {
        $sql = "SELECT * FROM perfis WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $this->perfil_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function atualizarUltimoLogin(int $id): bool {
        $sql = "UPDATE utilizadores 
            SET ultimo_login = NOW(), 
                tentativas_falhadas = 0,
                bloqueado_ate = NULL
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
