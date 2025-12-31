<?php
namespace app\Core;

use PDO;

class Versoes
{
    public static function guardar(object $doc): void
    {
        $db = Conexao::getInstancia();

        $sql = "INSERT INTO documentos_versoes 
                (documento_id, ficheiro, tipo, tamanho, criado_por)
                VALUES (:id, :ficheiro, :tipo, :tamanho, :uid)";

        $stm = $db->prepare($sql);
        $stm->execute([
            'id'       => $doc->id,
            'ficheiro' => $doc->ficheiro,
            'tipo'     => $doc->tipo,
            'tamanho'  => $doc->tamanho,
            'uid'      => Auth::id()
        ]);
    }
}