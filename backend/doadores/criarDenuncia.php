<?php
// criarDenuncia.php (doadores)
// POST body JSON: { idreserva: int, motivo: string }
// Registra uma denúncia associada ao doador logado e à reserva informada.

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => true, 'mensagem' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$idReserva = isset($body['idreserva']) ? (int) $body['idreserva'] : 0;
$motivo    = isset($body['motivo'])    ? trim($body['motivo'])     : '';
$idUsuario = (int) $_SESSION['idusuario'];

// Validações
if ($idReserva <= 0) {
    echo json_encode(['erro' => true, 'mensagem' => 'Reserva inválida.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($motivo) < 10) {
    echo json_encode(['erro' => true, 'mensagem' => 'O motivo deve ter pelo menos 10 caracteres.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($motivo) > 1000) {
    echo json_encode(['erro' => true, 'mensagem' => 'O motivo não pode ultrapassar 1000 caracteres.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Verifica se a reserva pertence a um alimento do doador logado
    $check = $pdo->prepare("
        SELECT r.IDRESERVA
        FROM Reserva r
        JOIN Alimento_doador ad ON ad.IDALIMENTO_DOADOR = r.IDALIMENTO_DOADOR
        WHERE r.IDRESERVA = :idreserva
          AND ad.IDUSUARIO = :idusuario
    ");
    $check->execute([':idreserva' => $idReserva, ':idusuario' => $idUsuario]);

    if (!$check->fetch()) {
        echo json_encode(['erro' => true, 'mensagem' => 'Reserva não encontrada ou sem permissão.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Insere a denúncia com IDUSUARIO = doador logado
    $stmt = $pdo->prepare("
    INSERT INTO Denuncia (DIA_HORA, MOTIVO, IDRECLAMADOR)
    VALUES (NOW(), :motivo, :idreclamador)"
    );
    $stmt->execute([
    ':motivo'       => $motivo,
    ':idreclamador' => $idUsuario,
]);

    echo json_encode([
        'erro'       => false,
        'mensagem'   => 'Denúncia enviada com sucesso.',
        'idDenuncia' => (int) $pdo->lastInsertId()
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => true, 'mensagem' => 'Erro interno: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
