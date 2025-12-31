<?php

namespace App\Core;

use PDO;

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function paginate(int $limit, int $offset)
    {
        $sql = "SELECT * FROM utilizadores ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function count()
    {
        return $this->db->query("SELECT COUNT(*) FROM utilizadores")->fetchColumn();
    }

    public function find(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizadores WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizadores WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO utilizadores (nome, email, senha, nivel, estado)
                VALUES (:nome, :email, :senha, :nivel, :estado)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update(int $id, array $data)
    {
        $sql = "UPDATE utilizadores SET 
                nome = :nome,
                email = :email,
                nivel = :nivel,
                estado = :estado
                " . (isset($data['senha']) ? ", senha = :senha" : "") . "
                WHERE id = :id";

        $data['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM utilizadores WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}