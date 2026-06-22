<?php
// cancelarReserva.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$idReserva = isset($body['idReserva']) ? (int) $body['idReserva'] : 0;
$idUsuario = (int) $_SESSION['idusuario'];

if ($idReserva <= 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID de reserva inválido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Busca a reserva garantindo que pertence ao usuário e ainda está com IDSTATUS = 2 (Reservado)
    $stmt = $pdo->prepare("
        SELECT IDRESERVA, QUANTIDADE_RESERVADA, IDALIMENTO_DOADOR
        FROM   Reserva
        WHERE  IDRESERVA = :idReserva
          AND  IDUSUARIO = :idusuario
          AND  IDSTATUS  = 2
        FOR UPDATE
    ");
    $stmt->execute([':idReserva' => $idReserva, ':idusuario' => $idUsuario]);
    $reserva = $stmt->fetch();

    if (!$reserva) {
        $pdo->rollBack();
        echo json_encode(['erro' => 'Reserva não encontrada ou não pode ser cancelada.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Atualiza status para 3 = Cancelado
    $stmtUpd = $pdo->prepare("UPDATE Reserva SET IDSTATUS = 3 WHERE IDRESERVA = :idReserva");
    $stmtUpd->execute([':idReserva' => $idReserva]);

    // 3. Devolve a quantidade ao alimento original
    $stmtDev = $pdo->prepare("
        UPDATE Alimento_doador
        SET    QUANTIDADE = QUANTIDADE + :qtd
        WHERE  IDALIMENTO_DOADOR = :idAlimentoDoador
    ");
    $stmtDev->execute([
        ':qtd'             => $reserva['QUANTIDADE_RESERVADA'],
        ':idAlimentoDoador'=> $reserva['IDALIMENTO_DOADOR'],
    ]);

    $pdo->commit();
    echo json_encode(['sucesso' => true], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
