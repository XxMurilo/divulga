<?php 
header('Content-Type: application/json; charset=utf-8');
require_once('../conexaobd.php');

try {
    $idCondicao = isset($_POST['idCondicao']) ? $_POST['idCondicao'] : '';
    $idUsuario = isset($_POST['idUsuario']) ? $_POST['idUsuario'] : '';
    $idCondicao = trim($idCondicao);
    $idUsuario - trim($idUsuario);

    if ($idCondicao === "") {
        echo json_encode(
            [
                "erro" => true,
                "mensagem" => "O ID da Condição deve ser informado"
            ],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
        exit;
    }

    if ($idUsuario === "") {
        echo json_encode(
            [
                "erro" => true,
                "mensagem" => "O ID do Usuario deve ser informado"
            ],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
        exit;
    }

    $sql = "UPDATE Usuario SET IDCONDICAO = :idCondicao WHERE IDUSUARIO = :idUsuario";
    $comando = $pdo->prepare($sql);
    $comando->bindValue(':idUsuario', $idUsuario, PDO::PARAM_STR);
    $comando->bindValue(':idCondicao', $idCondicao, PDO::PARAM_INT);
    $comando->execute();
    echo json_enconde(
        array(
            "erro" => false,
            "mensagem" => "Condição alterada com sucesso.",
            "IDCONDICAO" => $idCondicao,
            "IDUSUARIO" => $idUsuario
        ),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    )
} catch(PDOException $e) {
    echo json_encode(
        array(
            "erro" => true,
            "mensagem" => "Erro ao alterar Condição.",
            "detalhes" => $e->getMessage();
        ),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    )
}