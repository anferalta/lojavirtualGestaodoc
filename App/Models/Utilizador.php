<?php

namespace App\Core;

use PDO;
use PDOException;

class Utilizador
{
    private PDO $db;
    private string $table = 'utilizadores';

    // Campos permitidos para update
    private array $permitidos = ['nome', 'email', 'senha', 'nivel', 'estado'];

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
     * Validação de campos obrigatórios
     */
    private function validar(array $dados, array $obrigatorios): bool
    {
        foreach ($obrigatorios as $campo) {
            if (!isset($dados[$campo]) || $dados[$campo] === '') {
                AuditLogger::log('erro_validacao', "Campo obrigatório em falta: $campo");
                return false;
            }
        }
        return true;
    }

    /**
     * Obter todos os utilizadores (exceto soft deleted)
     */
    public function all(): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE estado != -1 ORDER BY id DESC";
            $stmt = $this->db->query($sql);

            return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return [];
        }
    }

    /**
     * Paginação
     */
    public function paginate(int $limit, int $offset): array
    {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE estado != -1
                    ORDER BY id DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return [];
        }
    }

    /**
     * Contar total de utilizadores (exceto soft deleted)
     */
    public function count(): int
    {
        try {
            return (int) $this->db
                ->query("SELECT COUNT(*) FROM {$this->table} WHERE estado != -1")
                ->fetchColumn();
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return 0;
        }
    }

    /**
     * Encontrar utilizador por ID
     */
    public function find(int $id): ?object
    {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE id = :id AND estado != -1
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? $this->map($data) : null;
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return null;
        }
    }

    /**
     * Encontrar utilizador por email
     */
    public function findByEmail(string $email): ?object
    {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE email = :email AND estado != -1
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? $this->map($data) : null;
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar se um valor existe num campo
     */
    public function exists(string $campo, $valor): bool
    {
        if (!in_array($campo, $this->permitidos, true)) {
            return false;
        }

        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}
                    WHERE $campo = :valor AND estado != -1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':valor' => $valor]);

            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return false;
        }
    }

    /**
     * Pesquisa por nome ou email
     */
    public function search(string $termo): array
    {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE estado != -1
                    AND (nome LIKE :t OR email LIKE :t)
                    ORDER BY id DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':t' => "%$termo%"]);

            return array_map(fn($row) => $this->map($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return [];
        }
    }

    /**
     * Criar utilizador
     */
    public function create(array $dados): bool
    {
        if (!$this->validar($dados, ['nome', 'email', 'senha', 'nivel', 'estado'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO {$this->table}
                    (nome, email, senha, nivel, estado)
                    VALUES (:nome, :email, :senha, :nivel, :estado)";

            $stmt = $this->db->prepare($sql);

            $ok = $stmt->execute([
                ':nome'   => $dados['nome'],
                ':email'  => $dados['email'],
                ':senha'  => $dados['senha'],
                ':nivel'  => $dados['nivel'],
                ':estado' => $dados['estado'],
            ]);

            if ($ok) {
                AuditLogger::log('utilizador_criado', "Email: {$dados['email']}");
            }

            return $ok;
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar utilizador
     */
    public function update(int $id, array $dados): bool
    {
        $campos = [];
        $params = [':id' => $id];

        foreach ($dados as $campo => $valor) {
            if (!in_array($campo, $this->permitidos, true)) {
                continue;
            }

            $campos[] = "$campo = :$campo";
            $params[":$campo"] = $valor;
        }

        if (empty($campos)) {
            return false;
        }

        try {
            $sql = "UPDATE {$this->table}
                    SET " . implode(', ', $campos) . "
                    WHERE id = :id AND estado != -1";

            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute($params);

            if ($ok) {
                AuditLogger::log('utilizador_atualizado', "ID: $id");
            }

            return $ok;
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete (estado = -1)
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "UPDATE {$this->table}
                    SET estado = -1
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([':id' => $id]);

            if ($ok) {
                AuditLogger::log('utilizador_eliminado', "ID: $id");
            }

            return $ok;
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar último login
     */
    public function updateLastLogin(int $id): void
    {
        try {
            $sql = "UPDATE {$this->table}
                    SET ultimo_login = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            AuditLogger::log('utilizador_login', "ID: $id");
        } catch (PDOException $e) {
            AuditLogger::log('erro_bd', $e->getMessage());
        }
    }
}