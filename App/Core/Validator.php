<?php
namespace App\Core;

use PDO;

class Validator
{
    private array $errors = [];

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function required(string $field, $value, string $message): void
    {
        if ($value === null || trim((string)$value) === '') {
            $this->addError($field, $message);
        }
    }

    public function email(string $field, $value, string $message): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $message);
        }
    }

    public function min(string $field, $value, int $min, string $message): void
    {
        if (strlen((string)$value) < $min) {
            $this->addError($field, $message);
        }
    }

    public function max(string $field, $value, int $max, string $message): void
    {
        if (strlen((string)$value) > $max) {
            $this->addError($field, $message);
        }
    }

    public function match(string $field, $value, $otherValue, string $message): void
    {
        if ($value !== $otherValue) {
            $this->addError($field, $message);
        }
    }

    public function numeric(string $field, $value, string $message): void
    {
        if (!is_numeric($value)) {
            $this->addError($field, $message);
        }
    }

    public function in(string $field, $value, array $allowed, string $message): void
    {
        if (!in_array($value, $allowed, true)) {
            $this->addError($field, $message);
        }
    }

    public function unique(string $field, $value, PDO $db, string $table, string $column, string $message): void
    {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value LIMIT 1";
        $stm = $db->prepare($sql);
        $stm->execute(['value' => $value]);

        if ($stm->fetchColumn() > 0) {
            $this->addError($field, $message);
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
}