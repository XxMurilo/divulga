<?php
require_once '../conexaoBD.php';

header('Content-Type: application/json; charset=utf-8');

$contem = $_GET['texto'] ?? '';
$tabelaP = $_GET['table'] ?? '';

try {

    if ($tabelaP == 'food') {
        $tabela = 'VerAlimentos';

    } elseif ($tabelaP == 'user') {
        $tabela = 'VerUsuarios';

    } elseif ($tabelaP == 'complaint') {
        $tabela = 'VerDenuncias';

    } else {
        echo json_encode(["erro" => "Tabela não encontrada"]);
        exit;
    }

    // Busca nomes das colunas
    $colunasStmt = $pdo->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :tabela
        AND DATA_TYPE IN ('varchar', 'text', 'char', 'mediumtext', 'longtext')
    ");

    $colunasStmt->execute([
        ':tabela' => $tabela
    ]);

    $colunas = $colunasStmt->fetchAll(PDO::FETCH_COLUMN);

    // Monta os LIKE automaticamente
    $where = [];

    foreach ($colunas as $coluna) {
        $where[] = "$coluna LIKE :texto";
    }

    $whereSql = implode(' OR ', $where);

    // SQL final
    $sql = "SELECT * FROM $tabela";

    if (!empty($contem)) {
        $sql .= " WHERE $whereSql";
    }

    $stmt = $pdo->prepare($sql);

    if (!empty($contem)) {
        $stmt->bindValue(':texto', "%$contem%");
    }

    $stmt->execute();

    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($dados);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}