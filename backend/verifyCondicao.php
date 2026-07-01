<?php
require_once 'conexaoBD.php';
header('Content-Type: application/json; charset=utf-8');

$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

$idUser = $data['id'] ?? '';

// Se o ID vier vazio, cancela a execução e já avisa o JS
if (empty($idUser)) {
    echo json_encode(
        array(
            "erro" => true,
            "mensagem" => 'ID do usuário não foi fornecido.',
            "login" => false
        ),
        JSON_UNESCAPED_UNICODE
    );
    exit; // Para a execução do script aqui
}

try {
    $sql = 'SELECT IDCONDICAO FROM usuario WHERE IDUSUARIO=:idUser';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idUser', $idUser);
    $stmt->execute();

    $condicao = $stmt->fetch(PDO::FETCH_ASSOC);

    if($condicao && $condicao['IDCONDICAO'] == 1) {
        echo json_encode(
            array(
                "erro" => false,
                "mensagem" => 'Usuário ativo.',
                "login" => true
            ),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    } else {
        echo json_encode(
            array(
                "erro" => true,
                "mensagem" => 'Esse usuário foi desativado, entre em contato para verificar o motivo.',
                "login" => false
            ),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }
} catch (PDOException $e) {
    echo json_encode(
        array(
            "SystemError" => true,
            "mensagem" => $e->getMessage()
        ),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
}