<?php

namespace app\Model;

use app\Core\Model;
use PDO;

class UsuarioModel extends Model
{
    protected static string $tabela = 'usuarios';

    public int $id;
    public string $nome;
    public string $email;
    public string $senha;
    public int $level;
    public int $status;
    public string $cadastrado_em;

    /**
     * Salvar novo usuário
     */
    public function salvar(): bool
    {
        $sql = "INSERT INTO " . self::$tabela . " 
                (nome, email, senha, level, status, cadastrado_em) 
                VALUES (:nome, :email, :senha, :level, :status, :cadastrado_em)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome'          => $this->nome,
            ':email'         => $this->email,
            ':senha'         => $this->senha,
            ':level'         => $this->level,
            ':status'        => $this->status,
            ':cadastrado_em' => $this->cadastrado_em
        ]);
    }

    /**
     * Buscar usuário por ID
     */
    public static function buscarPorId(int $id): ?UsuarioModel
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id = :id LIMIT 1";
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute([':id' => $id]);

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dados) {
            $usuario = new self();
            foreach ($dados as $campo => $valor) {
                $usuario->$campo = $valor;
            }
            return $usuario;
        }
        return null;
    }

    /**
     * Atualizar usuário existente
     */
    public function atualizar(): bool
    {
        $sql = "UPDATE " . self::$tabela . " 
                SET nome = :nome, email = :email, senha = :senha, 
                    level = :level, status = :status 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome'   => $this->nome,
            ':email'  => $this->email,
            ':senha'  => $this->senha,
            ':level'  => $this->level,
            ':status' => $this->status,
            ':id'     => $this->id
        ]);
    }

    /**
     * Deletar usuário
     */
    public function deletar(): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $this->id]);
    }
}