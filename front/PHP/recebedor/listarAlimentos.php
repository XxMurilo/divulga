<?php
// listarAlimentosDisponiveis.php
// Retorna alimentos cadastrados por doadores com quantidade > 0
// GET /PHP/recebedor/listarAlimentosDisponiveis.php

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    /*
     * Junta:
     *   Alimento_doador  (IDALIMENTO_DOADOR, VALIDADE, QUANTIDADE, DESCRICAO, IDUSUARIO, IDALIMENTO)
     *   Alimento         (NOME do alimento)
     *   Usuario (doador) (NOME, ENDERECO)
     *
     * Filtra apenas alimentos com QUANTIDADE > 0 e validade >= hoje.
     * Exclui os próprios alimentos do usuário logado (recebedor não vê a si mesmo, mas aqui
     * não seria problema pois recebedor não cadastra alimentos).
     */
    $stmt = $pdo->prepare("
    SELECT
        ad.IDALIMENTO_DOADOR AS idAlimentoDoador,
        a.NOME               AS nomeAlimento,
        ad.QUANTIDADE        AS quantidade,
        ad.DESCRICAO         AS descricao,
        ad.VALIDADE          AS validade,
        u.NOME               AS nomeDoador,
        u.ENDERECO           AS enderecoDoador

    FROM Alimento_doador ad

    INNER JOIN Alimento a
        ON a.IDALIMENTO = ad.IDALIMENTO

    INNER JOIN Usuario u
        ON u.IDUSUARIO = ad.IDUSUARIO

    WHERE
        ad.QUANTIDADE > 0

    ORDER BY
        ad.IDALIMENTO_DOADOR DESC
");
    $stmt->execute();
    $alimentos = $stmt->fetchAll();

    echo json_encode(['sucesso' => true, 'alimentos' => $alimentos], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}