<?php

namespace App\Core;

class Validator
{
    private array $errors = [];

    private function addError(string $campo, string $msg): void
    {
        $this->errors[$campo][] = $msg;
    }

    public function required(string $campo, ?string $valor, string $msg): void
    {
        if (trim((string) $valor) === '') {
            $this->addError($campo, $msg);
        }
    }

    public function min(string $campo, string $valor, int $min, string $msg): void
    {
        if (mb_strlen($valor, 'UTF-8') < $min) {
            $this->addError($campo, $msg);
        }
    }

    public function max(string $campo, string $valor, int $max, string $msg): void
    {
        if (mb_strlen($valor, 'UTF-8') > $max) {
            $this->addError($campo, $msg);
        }
    }

    public function email(string $campo, string $valor, string $msg): void
    {
        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->addError($campo, $msg);
        }
    }

    public function match(string $campo, string $v1, string $v2, string $msg): void
    {
        if ($v1 !== $v2) {
            $this->addError($campo, $msg);
        }
    }

    public function in(string $campo, string $valor, array $lista, string $msg): void
    {
        if (!in_array($valor, $lista, true)) {
            $this->addError($campo, $msg);
        }
    }

    public function numeric(string $campo, $valor, string $msg): void
    {
        if (!is_numeric($valor)) {
            $this->addError($campo, $msg);
        }
    }

    public function integer(string $campo, $valor, string $msg): void
    {
        if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
            $this->addError($campo, $msg);
        }
    }

    public function regex(string $campo, string $valor, string $pattern, string $msg): void
    {
        if (!preg_match($pattern, $valor)) {
            $this->addError($campo, $msg);
        }
    }

    public function unique(
        string $campo,
        string $valor,
        \PDO $db,
        string $tabela,
        string $coluna,
        string $msg,
        ?int $ignoreId = null
    ): void {
        $sql = "SELECT COUNT(*) AS total FROM {$tabela} WHERE {$coluna} = :v";

        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':v', $valor);

        if ($ignoreId !== null) {
            $stmt->bindValue(':id', $ignoreId, \PDO::PARAM_INT);
        }

        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        if ($total > 0) {
            $this->addError($campo, $msg);
        }
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(string $campo): ?string
    {
        return $this->errors[$campo][0] ?? null;
    }
}