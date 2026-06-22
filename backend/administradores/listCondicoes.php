<?php 
header('Content-Type: application/json; charset=utf-8');
require_once('../conexaobd.php');

try {
    $sql = "SELECT IDCONDICAO, NOME FROM condicao ORDER BY IDCONDICAO";
    $comando = $pdo->prepare($sql);
    $comando->execute();
    $condicoes = $comando->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($condicoes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
catch(PDOException $erro) {
    echo json_encode(
        [
            "erro" => true,
            "mensagem" => "Erro ao listar condições",
            "detalhes" => $erro->getMessage()
        ],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
}