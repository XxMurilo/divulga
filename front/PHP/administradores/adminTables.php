<?php
require_once '../conexaoBD.php';

header('Content-Type: application/json; charset=utf-8');

$tabelaP = $_GET['table'] ?? '';

try {

    if ($tabelaP == 'food') {
        $tabela = 'VerAlimentos()';

    } elseif ($tabelaP == 'user') {
        $tabela = 'VerUsuarios()';

    } elseif ($tabelaP == 'complaint') {
        $tabela = 'Denuncia';

    } else {
        echo json_encode(["erro" => "Tabela não encontrada"]);
        exit;
    }

    $stmt = $pdo->prepare("call $tabela");
    $stmt->execute();

    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($dados);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
}