<?php

namespace app\Suporte;

class Template
{
    private string $diretorio;

    public function __construct(string $diretorio = 'views')
    {
        $this->diretorio = $diretorio;
    }

    /**
     * Renderiza uma view e injeta variáveis
     */
    public function render(string $view, array $data = []): string
    {
        $caminho = __DIR__ . "/../Views/{$view}.php";

        if (!file_exists($caminho)) {
            throw new \Exception("View '{$view}' não encontrada em {$caminho}");
        }

        // Extrai as variáveis do array para uso direto na view
        extract($data);

        // Captura o output da view
        ob_start();
        include $caminho;
        return ob_get_clean();
    }
}