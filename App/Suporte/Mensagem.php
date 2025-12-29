<?php

namespace App\Suporte;

class Mensagem
{
    private array $mensagens = [];

    public function set(string $tipo, string $conteudo): void
    {
        $this->mensagens[] = [
            'tipo' => $tipo,
            'conteudo' => $conteudo
        ];
    }

    public function getAll(): array
    {
        return $this->mensagens;
    }

    public function clear(): void
    {
        $this->mensagens = [];
    }
}