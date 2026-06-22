<?php
//alterarALimentos.php
//Este arquivo é responsável por alterar os dados de um alimento específico do doador logado.

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
            'mensagem' => 'Parâmetro "idalimento" é obrigatório para alteração.'
        ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    exit;
}

$idalimento = intval($_GET['idalimento']);


try{

    if (!isset($_POST['quantidade']) || !isset($_POST['validade']) || !isset($_POST['descricao'])) {
        echo json_encode(
            [
                'erro' => true,
                'mensagem' => 'Parâmetros "quantidade", "validade" e "descricao" são obrigatórios para alteração.'
             ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
        exit;
    }
     
    $quantidade = intval($_POST['quantidade']);
    $validade = trim($_POST['validade']);
    $descricao = trim($_POST['descricao']);

    $validadeDate = DateTime::createFromFormat('Y-m-d', $validade);
    if (!$validadeDate || $validadeDate->format('Y-m-d') !== $validade) {
        echo json_encode(
            [
                'erro' => true,
                'mensagem' => 'O campo "validade" deve estar no formato YYYY-MM-DD.'
             ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
        exit;
    }

    $sql = "UPDATE Alimento_doador 
            SET VALIDADE = :validade, QUANTIDADE = :quantidade, DESCRICAO = :descricao 
            WHERE IDALIMENTO_DOADOR = :idalimento AND IDUSUARIO = :idusuario";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':validade', $validade, PDO::PARAM_STR);
    $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindParam(':idalimento', $idalimento, PDO::PARAM_INT);
    $stmt->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(
            [
                'sucesso' => true,
                'mensagem' => 'Alimento alterado com sucesso.'
             ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(
            [
                'erro' => true,
                'mensagem' => 'Alimento não encontrado ou você não tem permissão para alterar.'
             ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    }

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