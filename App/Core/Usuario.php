<?php

namespace App\Core;

use PDO;

class Usuario {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /*
      |--------------------------------------------------------------------------
      | FINDERS
      |--------------------------------------------------------------------------
     */

    public function findByEmail(string $email) {
        $stm = $this->db->prepare("
            SELECT * FROM utilizadores 
            WHERE email = :email 
            LIMIT 1
        ");
        $stm->execute(['email' => trim($email)]);
        return $stm->fetch(PDO::FETCH_OBJ);
    }

    public function findByEmailExceptId(string $email, int $id) {
        $stm = $this->db->prepare("
            SELECT * FROM utilizadores 
            WHERE email = :email AND id != :id 
            LIMIT 1
        ");
        $stm->execute(['email' => trim($email), 'id' => $id]);
        return $stm->fetch(PDO::FETCH_OBJ);
    }

    public function find(int $id) {
        $stm = $this->db->prepare("
            SELECT * FROM utilizadores 
            WHERE id = :id 
            LIMIT 1
        ");
        $stm->execute(['id' => $id]);
        return $stm->fetch(PDO::FETCH_OBJ);
    }

    /*
      |--------------------------------------------------------------------------
      | CRUD
      |--------------------------------------------------------------------------
     */

    public function create(array $data): int {
        $stm = $this->db->prepare("
            INSERT INTO utilizadores (nome, email, senha, perfil_id)
            VALUES (:nome, :email, :senha, :perfil)
        ");

        $stm->execute([
            'nome' => trim($data['nome']),
            'email' => trim($data['email']),
            'senha' => $data['senha'], // já vem com hash
            'perfil' => $data['perfil_id'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        // Construir dinamicamente os campos a atualizar
        $campos = [];
        $valores = [];

        foreach ($data as $campo => $valor) {
            $campos[] = "$campo = :$campo";
            $valores[$campo] = $valor;
        }

        // Adicionar o ID
        $valores['id'] = $id;

        // Construir SQL final
        $sql = "UPDATE utilizadores SET " . implode(', ', $campos) . " WHERE id = :id";

        $stm = $this->db->prepare($sql);
        $stm->execute($valores);
    }

    public function delete(int $id): void {
        $stm = $this->db->prepare("DELETE FROM utilizadores WHERE id = :id");
        $stm->execute(['id' => $id]);
    }

    /*
      |--------------------------------------------------------------------------
      | PAGINAÇÃO
      |--------------------------------------------------------------------------
     */

    public function paginate(int $limit, int $offset) {
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

    /*
      |--------------------------------------------------------------------------
      | PASSWORD / LOGIN
      |--------------------------------------------------------------------------
     */

    public function updatePasswordByEmail(string $email, string $hash): void {
        $stm = $this->db->prepare("
            UPDATE utilizadores 
            SET senha = :senha 
            WHERE email = :email
        ");
        $stm->execute(['senha' => $hash, 'email' => trim($email)]);
    }

    /*
      |--------------------------------------------------------------------------
      | 2FA
      |--------------------------------------------------------------------------
     */

    public function ativar2FA(int $id, string $secret): void {
        $stm = $this->db->prepare("
            UPDATE utilizadores 
            SET two_factor_secret = :secret, two_factor_ativo = 1 
            WHERE id = :id
        ");
        $stm->execute(['secret' => $secret, 'id' => $id]);
    }

    public function desativar2FA(int $id): void {
        $stm = $this->db->prepare("
            UPDATE utilizadores 
            SET two_factor_secret = NULL, two_factor_ativo = 0 
            WHERE id = :id
        ");
        $stm->execute(['id' => $id]);
    }

    /*
      |--------------------------------------------------------------------------
      | LOGIN SECURITY
      |--------------------------------------------------------------------------
     */

    public function atualizarUltimoLogin(int $id): void {
        $stm = $this->db->prepare("
            UPDATE utilizadores 
            SET ultimo_login = NOW() 
            WHERE id = :id
        ");
        $stm->execute(['id' => $id]);
    }

    public function atualizarTentativas(int $id, int $tentativas): void {
        $stm = $this->db->prepare("
            UPDATE utilizadores 
            SET tentativas_falhadas = :t 
            WHERE id = :id
        ");
        $stm->execute(['t' => $tentativas, 'id' => $id]);
    }

    public function bloquear(int $id, string $ate): void {
        $stm = $this->db->prepare("
            UPDATE utilizadores 
            SET bloqueado_ate = :ate 
            WHERE id = :id
        ");
        $stm->execute(['ate' => $ate, 'id' => $id]);
    }
}
