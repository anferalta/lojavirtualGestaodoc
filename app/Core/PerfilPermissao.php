public function allGrouped()
{
    $rows = $this->query("SELECT * FROM perfil_permissoes")->fetchAll();

    $result = [];
    foreach ($rows as $row) {
        $result[$row['perfil_id']][] = $row['permissao_id'];
    }

    return $result;
}