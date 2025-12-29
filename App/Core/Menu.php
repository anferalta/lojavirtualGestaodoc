<?php
namespace App\Core;

use App\Core\Helpers;

class Menu
{
    private array $items = [
        [
            'label' => 'Dashboard',
            'icon'  => '📊',
            'url'   => '/painel',
            'perm'  => null
        ],
        [
            'label' => 'Utilizadores',
            'icon'  => '👤',
            'url'   => '/utilizadores',
            'perm'  => 'utilizadores.ver'
        ],
        [
            'label' => 'Documentos',
            'icon'  => '📁',
            'url'   => '/documentos',
            'perm'  => 'documentos.ver'
        ],
        [
            'label' => 'Perfis & Permissões',
            'icon'  => '🔐',
            'url'   => '/permissoes',
            'perm'  => 'permissoes.ver'
        ]
    ];

    public function render(): string
    {
        $html = '<ul class="menu">';

        foreach ($this->items as $item) {

            if ($item['perm'] && !Helpers::can($item['perm'])) {
                continue;
            }

            $html .= "
                <li>
                    <a href=\"{$item['url']}\">
                        <span class=\"icon\">{$item['icon']}</span>
                        {$item['label']}
                    </a>
                </li>
            ";
        }

        $html .= '</ul>';

        return $html;
    }
}