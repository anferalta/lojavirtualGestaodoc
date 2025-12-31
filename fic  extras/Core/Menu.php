<?php

namespace app\Core;

class Menu
{
    public function itens(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'icon'  => 'fa fa-home',
                'url'   => Helpers::url('/dashboard'),
                'perm'  => null
            ],
            [
                'label' => 'Utilizadores',
                'icon'  => 'fa fa-users',
                'url'   => Helpers::url('/utilizadores'),
                'perm'  => 'utilizadores.ver'
            ],
        ];
    }
}