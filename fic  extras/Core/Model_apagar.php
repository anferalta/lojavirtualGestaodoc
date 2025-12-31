<?php

namespace app\Core;

use PDO;
use PDOException;

abstract class Model
{
    protected ?int $id = null;
    protected array $dados = [];
    protected ?string $erro = null;
    protected array $parametros = [];
    protected string $ordem = '';
    protected string $limite = '';
    protected string $offset = '';
    protected $mensagem;
    protected static string $tabela;

    public function __construct()
    {
        $this->mensagem = new Mensagem();
    }

    public function salvar(): bool
    {
        if (empty($this->id)) {
            $id = $this->cadastrar($this->armazenar());
            if ($this->erro) {
                $this->mensagem->erro('Erro ao cadastrar');
                return false;
            }
        } else {
            $id = $this->id;
            $this->atualizar($this->armazenar(), "id = :id", [':id' => $id]);
            if ($this->erro) {
                $this->mensagem->erro('Erro ao atualizar');
                return false;
            }
        }

        $obj = $this->buscaPorId($id);
        $this->dados = $obj ? (array) $obj : [];
        return true;
    }

    protected function cadastrar(array $dados): ?int
    {
        try {
            $colunas = implode(',', array_keys($dados));
            $valores = ':' . implode(',:', array_keys($dados));
            $query = "INSERT INTO " . static::$tabela . " ({$colunas}) VALUES ({$valores})";

            $stmt = Database::getConexao()->prepare($query);
            $stmt->execute($this->filtro($dados));

            return (int) Database::getConexao()->lastInsertId();
        } catch (PDOException $ex) {
            $this->erro = $ex->getMessage();
            return null;
        }
    }

    public function buscaPorId(int $id): ?object
    {
        $query = "SELECT * FROM " . static::$tabela . " WHERE id = :id LIMIT 1";
        $stmt = Database::getConexao()->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchObject(static::class) ?: null;
    }

    /**
     * Exemplo de filtro para limpar dados antes de inserir
     */
    protected function filtro(array $dados): array
    {
        return array_map(fn($valor) => is_string($valor) ? trim($valor) : $valor, $dados);
    }

    /**
     * Exemplo de armazenar dados (precisas adaptar ao teu caso)
     */
    protected function armazenar(): array
    {
        return $this->dados;
    }
}