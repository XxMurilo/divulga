<?php
// criarReserva.php
// POST body JSON: { idAlimentoDoador: int, quantidade: int }
// Desconta a quantidade do alimento e cria a reserva com status 1 (Ativo/Pendente)

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$idAlimentoDoador = isset($body['idAlimentoDoador']) ? (int) $body['idAlimentoDoador'] : 0;
$quantidade       = isset($body['quantidade'])       ? (int) $body['quantidade']       : 0;
$idUsuario        = (int) $_SESSION['idusuario'];

if ($idAlimentoDoador <= 0 || $quantidade <= 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Busca o alimento e trava a linha para atualização
    $stmt = $pdo->prepare("
        SELECT IDALIMENTO_DOADOR, QUANTIDADE
        FROM   Alimento_doador
        WHERE  IDALIMENTO_DOADOR = :id
        FOR UPDATE
    ");
    $stmt->execute([':id' => $idAlimentoDoador]);
    $alimento = $stmt->fetch();

    if (!$alimento) {
        $pdo->rollBack();
        echo json_encode(['erro' => 'Alimento não encontrado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($alimento['QUANTIDADE'] < $quantidade) {
        $pdo->rollBack();
        echo json_encode([
            'erro' => "Quantidade insuficiente. Disponível: {$alimento['QUANTIDADE']}."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Decrementa a quantidade do alimento
    $stmtUpd = $pdo->prepare("
        UPDATE Alimento_doador
        SET    QUANTIDADE = QUANTIDADE - :qtd
        WHERE  IDALIMENTO_DOADOR = :id
    ");
    $stmtUpd->execute([':qtd' => $quantidade, ':id' => $idAlimentoDoador]);

    // 3. Cria a reserva (IDSTATUS = 2 = Reservado)
    $stmtRes = $pdo->prepare("
        INSERT INTO Reserva (QUANTIDADE_RESERVADA, IDSTATUS, IDUSUARIO, IDALIMENTO_DOADOR)
        VALUES (:qtd, 2, :idusuario, :idAlimentoDoador)
    ");
    $stmtRes->execute([
        ':qtd'             => $quantidade,
        ':idusuario'       => $idUsuario,
        ':idAlimentoDoador'=> $idAlimentoDoador,
    ]);

    $pdo->commit();
    echo json_encode(['sucesso' => true, 'idReserva' => $pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}