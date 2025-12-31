<?php
namespace app\Core;

use PDO;

class Log
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexao::getInstancia();
    }

    /**
     * Regista uma ação no sistema
     */
    public function registrar(string $acao, string $detalhes = ''): void
    {
        Auditoria::log(Auth::id(), 'ver', $id);
        $sql = "INSERT INTO logs (user_id, acao, detalhes, data)
                VALUES (:user_id, :acao, :detalhes, NOW())";

        $stm = $this->db->prepare($sql);

        $stm->execute([
            'user_id' => Sessao::get('user_id') ?? null,
            'acao'       => $acao,
            'detalhes'   => $detalhes
        ]);
    }

    /**
     * Lista os logs mais recentes
     */
    public function listar(int $limit = 100): array
    {
        $sql = "SELECT l.*, u.nome AS usuario_nome
                FROM logs l
                LEFT JOIN utilizadores u ON u.id = l.user_id
                ORDER BY l.id DESC
                LIMIT :lim";

        $stm = $this->db->prepare($sql);
        $stm->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stm->execute();

        return $stm->fetchAll(PDO::FETCH_OBJ);
    }
}