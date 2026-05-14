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
            'mensagem' => 'Parâmetro "idalimento" é obrigatório para exclusão.'
        ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    exit;
}

$idalimento = intval($_GET['idalimento']);

try{
    $sql = "DELETE FROM Alimento WHERE IDALIMENTO = :idalimento AND IDUSUARIO = :idusuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idalimento', $idalimento, PDO::PARAM_INT);
    $stmt->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(
            [
                'sucesso' => true,
                'mensagem' => 'Alimento excluído com sucesso.'
             ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(
            [
                'erro' => true,
                'mensagem' => 'Alimento não encontrado ou você não tem permissão para excluir.'
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