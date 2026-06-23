<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => true, 'mensagem' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_GET['idalimento'])) {
    echo json_encode(['erro' => true, 'mensagem' => 'Parâmetro "idalimento" é obrigatório para exclusão.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$idalimento = intval($_GET['idalimento']);

try {
    // 1. Verifica se o alimento pertence ao doador logado
    $check = $pdo->prepare(
        "SELECT IDALIMENTO_DOADOR FROM Alimento_doador
         WHERE IDALIMENTO_DOADOR = :idalimento AND IDUSUARIO = :idusuario"
    );
    $check->execute([':idalimento' => $idalimento, ':idusuario' => $_SESSION['idusuario']]);

    if (!$check->fetch()) {
        echo json_encode(['erro' => true, 'mensagem' => 'Alimento não encontrado ou sem permissão.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Verifica se há reservas ATIVAS (status Reservado = 2)
    $checkReserva = $pdo->prepare(
        "SELECT COUNT(*) AS total FROM Reserva
         WHERE IDALIMENTO_DOADOR = :idalimento AND IDSTATUS = 2"
    );
    $checkReserva->execute([':idalimento' => $idalimento]);
    $totalAtivas = $checkReserva->fetch()['total'];

    if ($totalAtivas > 0) {
        echo json_encode([
            'erro'     => true,
            'mensagem' => 'Não é possível excluir: este alimento possui ' . $totalAtivas . ' reserva(s) ativa(s). Cancele ou confirme as reservas antes de excluir.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. Remove reservas canceladas/entregues vinculadas (para liberar a FK)
    $delReservas = $pdo->prepare("DELETE FROM Reserva WHERE IDALIMENTO_DOADOR = :idalimento");
    $delReservas->execute([':idalimento' => $idalimento]);

    // 4. Exclui o alimento
    $del = $pdo->prepare("DELETE FROM Alimento_doador WHERE IDALIMENTO_DOADOR = :idalimento");
    $del->execute([':idalimento' => $idalimento]);

    echo json_encode(['erro' => false, 'mensagem' => 'Alimento excluído com sucesso.'], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => true, 'mensagem' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
