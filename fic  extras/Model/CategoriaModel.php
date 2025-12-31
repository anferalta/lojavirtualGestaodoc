<?php
namespace App\Core;

use PDO;

class Categoria
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        return $this->db->query("SELECT * FROM documentos_categorias WHERE estado = 'ativo'")
                        ->fetchAll(PDO::FETCH_OBJ);
    }
}