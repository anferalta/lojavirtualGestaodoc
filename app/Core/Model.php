<?php

namespace App\Core;

use PDO;
use PDOException;
use App\Core\Database;

abstract class Model {

    protected static string $tabela;
    protected array $dados = [];
    protected ?string $erro = null;
    protected array $parametros = [];
    protected string $where = '';
    protected string $ordem = '';
    protected string $limite = '';
    protected string $offset = '';

    public function __construct() {
        // Construtor limpo — sem Mensagem
    }

    /* ============================================================
     *  MÉTODOS DE CONSULTA
     * ============================================================ */

    public function all(): array {
        $query = "SELECT * FROM " . static::$tabela
                . $this->where
                . $this->ordem
                . $this->limite
                . $this->offset;

        $stmt = Database::getConexao()->prepare($query);
        $stmt->execute($this->parametros);

        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    public function find(int $id): ?object {
        $query = "SELECT * FROM " . static::$tabela . " WHERE id = :id LIMIT 1";
        $stmt = Database::getConexao()->prepare($query);
        $stmt->execute([':id' => $id]);

        return $stmt->fetchObject(static::class) ?: null;
    }

    public function findBy(string $campo, $valor): ?object {
        $query = "SELECT * FROM " . static::$tabela . " WHERE {$campo} = :valor LIMIT 1";
        $stmt = Database::getConexao()->prepare($query);
        $stmt->execute([':valor' => $valor]);

        return $stmt->fetchObject(static::class) ?: null;
    }

    public function where(string $campo, string $operador, $valor): self {
        $this->where = " WHERE {$campo} {$operador} :where";
        $this->parametros[':where'] = $valor;
        return $this;
    }

    public function first(): ?object {
        $query = "SELECT * FROM " . static::$tabela
                . $this->where
                . $this->ordem
                . " LIMIT 1";

        $stmt = Database::getConexao()->prepare($query);
        $stmt->execute($this->parametros);

        return $stmt->fetchObject(static::class) ?: null;
    }

    public static function count() {
        $tabela = static::$tabela;
        $sql = "SELECT COUNT(*) AS total FROM {$tabela}";
        $stmt = Conexao::getInstancia()->query($sql);
        $row = $stmt->fetch();

        return is_array($row) ? ($row['total'] ?? 0) : ($row->total ?? 0);
    }

    public static function countWhere($condicao) {
        $tabela = static::$tabela;
        $sql = "SELECT COUNT(*) AS total FROM {$tabela} WHERE {$condicao}";
        $stmt = Conexao::getInstancia()->query($sql);
        $row = $stmt->fetch();

        return is_array($row) ? ($row['total'] ?? 0) : ($row->total ?? 0);
    }

    /* ============================================================
     *  INSERIR / ATUALIZAR / APAGAR
     * ============================================================ */

    public function insert(array $dados): ?int {
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

    public function update(array $dados, string $condicao, array $params): bool {
        try {
            $set = implode(', ', array_map(fn($c) => "{$c} = :{$c}", array_keys($dados)));

            $query = "UPDATE " . static::$tabela . " SET {$set} WHERE {$condicao}";

            $stmt = Database::getConexao()->prepare($query);

            return $stmt->execute(array_merge($this->filtro($dados), $params));
        } catch (PDOException $ex) {
            $this->erro = $ex->getMessage();
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $query = "DELETE FROM " . static::$tabela . " WHERE id = :id";
            $stmt = Database::getConexao()->prepare($query);

            return $stmt->execute([':id' => $id]);
        } catch (PDOException $ex) {
            $this->erro = $ex->getMessage();
            return false;
        }
    }

    /* ============================================================
     *  ORDENAÇÃO E PAGINAÇÃO
     * ============================================================ */

    public function orderBy(string $campo, string $direcao = 'ASC'): self {
        $this->ordem = " ORDER BY {$campo} {$direcao}";
        return $this;
    }

    public function limit(int $limite): self {
        $this->limite = " LIMIT {$limite}";
        return $this;
    }

    public function offset(int $offset): self {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    public function paginate(int $porPagina, int $pagina = 1): array {
        $offset = ($pagina - 1) * $porPagina;

        $query = "SELECT * FROM " . static::$tabela
                . $this->where
                . $this->ordem
                . " LIMIT {$porPagina} OFFSET {$offset}";

        $stmt = Database::getConexao()->prepare($query);
        $stmt->execute($this->parametros);

        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    /* ============================================================
     *  UTILIDADES
     * ============================================================ */

    protected function filtro(array $dados): array {
        return array_map(fn($v) => is_string($v) ? trim($v) : $v, $dados);
    }

    public function erro(): ?string {
        return $this->erro;
    }

    public function avatarUrl(): string {
        return $this->avatar ? '/uploads/avatars/' . $this->avatar : '/assets/img/avatar-default.png';
    }

    public function estadoBadge(): string {
        return $this->estado == 1 ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>';
    }

    public function perfil() {
        if (empty($this->perfil_id)) {
            return null;
        }

        return (new \App\Models\Perfil())->find((int) $this->perfil_id);
    }
}
