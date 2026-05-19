<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => true, 'mensagem' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT NOME, EMAIL, TELEFONE, ENDERECO, IDENTIFICACAO
         FROM Usuario
         WHERE IDUSUARIO = :idusuario
         LIMIT 1"
    );
    $stmt->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();

    if (!$usuario) {
        echo json_encode(['erro' => true, 'mensagem' => 'Usuário não encontrado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(
        ['erro' => false, 'usuario' => [
            'nome'          => $usuario['NOME'],
            'email'         => $usuario['EMAIL'],
            'telefone'      => $usuario['TELEFONE'],
            'endereco'      => $usuario['ENDERECO'],
            'identificacao' => $usuario['IDENTIFICACAO'],
        ]],
        JSON_UNESCAPED_UNICODE
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => true, 'mensagem' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
