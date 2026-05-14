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

    if (!isset($_POST['nome']) || !isset($_POST['tipo_id'])) {
        echo json_encode(
            [
                'erro' => true,
                'mensagem' => 'Parâmetros "nome" e "tipo_id" são obrigatórios para alteração.'
             ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
        exit;
    }
     
    $nome = trim($_POST['nome']);
    $tipo_id = intval($_POST['tipo_id']);

    $sql = "UPDATE Alimento 
            SET NOME = :nome, IDTIPO = :tipo_id 
            WHERE IDALIMENTO = :idalimento AND IDUSUARIO = :idusuario";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindParam(':tipo_id', $tipo_id, PDO::PARAM_INT);
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