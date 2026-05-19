<?php
require_once 'conexaoBD.php';

$tabelaP = $_GET['table'] ?? '';

try {

    if ($tabelaP == 'food') {
        $tabela = 'Alimento';

    } elseif ($tabelaP == 'user') {
        $tabela = 'Usuario';

    } elseif ($tabelaP == 'complaint') {
        $tabela = 'Denuncia';

    } else {
        die('Tabela não encontrada.');
    }

    // Consulta
    $stmt = $pdo->prepare("SELECT * FROM $tabela");

    $stmt->execute();

    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');

    echo json_encode($dados);

} catch (PDOException $e) {

    echo 'Erro: ' . $e->getMessage();
}