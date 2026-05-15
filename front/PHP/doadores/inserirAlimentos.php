<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {

    http_response_code(401);

    echo json_encode([
        'erro' => true,
        'mensagem' => 'Usuário não autenticado.'
    ]);

    exit;
}

try {

    $nome = trim($_POST['nome'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');

    $quantidade = trim($_POST['quantidade'] ?? '');
    $validade = trim($_POST['validade'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    $idusuario = $_SESSION['idusuario'];

    // ── VERIFICA SE ALIMENTO JÁ EXISTE ──

    $sql = "SELECT IDALIMENTO
            FROM Alimento
            WHERE NOME = :nome
            AND IDTIPO = :tipo";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nome' => $nome,
        ':tipo' => $tipo
    ]);

    $alimento = $stmt->fetch();

    // ── SE NÃO EXISTIR, CRIA ──

    if (!$alimento) {

        $sql = "INSERT INTO Alimento
                (NOME, IDTIPO)
                VALUES
                (:nome, :tipo)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nome' => $nome,
            ':tipo' => $tipo
        ]);

        $idalimento = $pdo->lastInsertId();

    } else {

        $idalimento = $alimento['IDALIMENTO'];
    }

    // ── INSERE DOAÇÃO ──

    $sql = "INSERT INTO Alimento_doador
            (
                VALIDADE,
                QUANTIDADE,
                DESCRICAO,
                IDUSUARIO,
                IDALIMENTO
            )
            VALUES
            (
                :validade,
                :quantidade,
                :descricao,
                :idusuario,
                :idalimento
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':validade'   => $validade,
        ':quantidade' => $quantidade,
        ':descricao'  => $descricao,
        ':idusuario'  => $idusuario,
        ':idalimento' => $idalimento
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Alimento cadastrado com sucesso.'
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'erro' => true,
        'mensagem' => $e->getMessage()
    ]);
}