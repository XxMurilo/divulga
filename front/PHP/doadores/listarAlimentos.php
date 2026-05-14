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
         ],
         JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT
    );
    exit;
}

try{
    $sql = "SELECT ad.IDALIMENTO_DOADOR AS id,
                   a.NOME AS nome,
                   t.IDTIPO AS tipo_id,
                   t.NOME AS tipo,
                   ad.QUANTIDADE AS quantidade,
                   ad.VALIDADE AS validade,
                   ad.DESCRICAO AS descricao
            FROM Alimento_doador ad
            JOIN Alimento a ON ad.IDALIMENTO = a.IDALIMENTO
            JOIN Tipo t ON a.IDTIPO = t.IDTIPO
            WHERE ad.IDUSUARIO = :idusuario
            ORDER BY a.NOME ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(
        [
         'erro' => false,
         'alimentos' => $stmt->fetchAll()
         ], 
         JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT
    );

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(
        [
            'erro' => true,
            'mensagem' => $e->getMessage()
         ], 
         JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT
    );
    exit;
}



?>