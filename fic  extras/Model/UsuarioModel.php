<?php

namespace App\Model;

use app\Core\Conexao;
use PDO;

class UsuarioModel {

    private PDO $db;

    public function __construct() {
        $this->db = Conexao::getInstancia();
    }

    public function all(): array {
        return $this->db->query("SELECT * FROM utilizadores ORDER BY id DESC")
                        ->fetchAll(PDO::FETCH_OBJ);
    }

    public function paginate(int $limit, int $offset): array {
        $stm = $this->db->prepare("
            SELECT * FROM utilizadores 
            ORDER BY id DESC 
            LIMIT :limit OFFSET :offset
        ");

        $stm->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stm->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stm->execute();

        return $stm->fetchAll(PDO::FETCH_OBJ);
    }

    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM utilizadores")->fetchColumn();
    }

    public function find(int $id): ?object {
        $stm = $this->db->prepare("SELECT * FROM utilizadores WHERE id = :id LIMIT 1");
        $stm->execute(['id' => $id]);

        $user = $stm->fetch(PDO::FETCH_OBJ);
        return $user ?: null;
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO utilizadores (nome, email, senha, level)
                VALUES (:nome, :email, :senha, :level)";

        $stm = $this->db->prepare($sql);

        return $stm->execute([
                    'nome' => trim($data['nome']),
                    'email' => strtolower(trim($data['email'])),
                    'senha' => password_hash($data['senha'], PASSWORD_DEFAULT),
                    'level' => $data['level'] ?? 1
        ]);
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE utilizadores 
                SET nome = :nome, email = :email, level = :level";

        if (!empty($data['senha'])) {
            $sql .= ", senha = :senha";
        }

        $sql .= " WHERE id = :id";

        $stm = $this->db->prepare($sql);

        $params = [
            'nome' => trim($data['nome']),
            'email' => strtolower(trim($data['email'])),
            'level' => $data['level'] ?? 1,
            'id' => $id
        ];

        if (!empty($data['senha'])) {
            $params['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        return $stm->execute($params);
    }

    public function delete(int $id): bool {
        $stm = $this->db->prepare("DELETE FROM utilizadores WHERE id = :id");
        return $stm->execute(['id' => $id]);
    }

    public function isAtivo(): bool {
        return (int) $this->estado === 1;
    }

    public function nivelLabel(): string {
        return match ((int) $this->nivel) {
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
        return $this->ultimo_login ?? null;
    }
}
