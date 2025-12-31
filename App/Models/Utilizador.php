<?php

namespace app\Core;

use PDO;
use PDOException;

class Utilizador
{
    private PDO $db;
    private string $table = 'utilizadores';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Converte um array da BD num objeto Utilizador
     */
    private function map(array $data): object
    {
        return (object) [
            'id'           => (int) $data['id'],
            'nome'         => $data['nome'],
            'email'        => $data['email'],
            'senha'        => $data['senha'],
            'nivel'        => (int) $data['nivel'],
            'estado'       => (int) $data['estado'],
            'ultimo_login' => $data['ultimo_login'] ?? null,

            // Métodos de domínio
            'isAtivo' => fn() => (int) $data['estado'] === 1,

            'nivelLabel' => function () use ($data) {
                return match ((int) $data['nivel']) {
                    1 => 'Básico',
                    2 => 'Gestor',
                    3 => 'Administrador',
                    default => 'Desconhecido'
                };
            },

            'ultimoLogin' => fn() => $data['ultimo_login'] ?? null,
        ];
    }

    /**
     * Obter todos os utilizadores
     */
    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->db->query($sql);

        return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Paginação
     */
    public function paginate(int $limit, int $offset): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Contar total de utilizadores
     */
    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    /**
     * Encontrar utilizador por ID
     */
    public function find(int $id): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);

        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->map($data) : null;
    }

    /**
     * Encontrar utilizador por email (para login)
     */
    public function findByEmail(string $email): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);

        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->map($data) : null;
    }

    /**
     * Criar utilizador
     */
    public function create(array $dados): bool
    {
        $sql = "INSERT INTO {$this->table} (nome, email, senha, nivel, estado)
                VALUES (:nome, :email, :senha, :nivel, :estado)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nome'   => $dados['nome'],
            ':email'  => $dados['email'],
            ':senha'  => $dados['senha'],
            ':nivel'  => $dados['nivel'],
            ':estado' => $dados['estado'],
        ]);
    }

    /**
     * Atualizar utilizador
     */
    public function update(int $id, array $dados): bool
    {
        $campos = [];
        $params = [':id' => $id];

        foreach ($dados as $campo => $valor) {
            $campos[] = "$campo = :$campo";
            $params[":$campo"] = $valor;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Eliminar utilizador
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Atualizar último login
     */
    public function updateLastLogin(int $id): void
    {
        $sql = "UPDATE {$this->table} SET ultimo_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->execute([':id' => $id]);
    }
}