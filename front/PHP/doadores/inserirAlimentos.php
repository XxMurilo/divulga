<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(
        [
            'erro' => 'Não autorizado.',
            'mensagem' => 'Faça login para acessar este recurso.'
         ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    exit;
}

if (!isset($_GET['idalimento'])) {
    echo json_encode(
        [
            'erro' => true,
            'mensagem' => 'Parâmetro "idalimento" é obrigatório para Inserção da doação.'
        ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    exit;
}

try{

    if (!isset($_POST['quantidade']) || !isset($_POST['descricao'])) {
        echo json_encode(
            [
                'erro' => true,
                'mensagem' => 'Parâmetros "quantidade" e "descricao" são obrigatórios para Inserção da doação.'
             ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
        exit;
    }

    $idalimento = intval($_GET['idalimento']);
    $quantidade = intval($_POST['quantidade']);
    $descricao = trim($_POST['descricao']);

    $sql = "INSERT INTO Doacao (IDALIMENTO, IDUSUARIO, QUANTIDADE, DESCRICAO) 
            VALUES (:idalimento, :idusuario, :quantidade, :descricao)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idalimento', $idalimento, PDO::PARAM_INT);
    $stmt->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
    $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->execute();

    json_encode(
        [
            'sucesso' => true,
            'mensagem' => 'Doação inserida com sucesso.'
         ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);


} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(
        [
            'erro' => true,
            'mensagem' => $e->getMessage()
         ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    exit;
}

?>