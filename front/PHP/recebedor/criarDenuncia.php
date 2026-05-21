<?php
// criarDenuncia.php
// POST body JSON: { motivo: string, idUsuarioCulpado: int }
// IDRECLAMADOR = usuário logado | IDDENUNCIADO = doador da reserva

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$motivo           = isset($body['motivo'])           ? trim($body['motivo'])           : '';
$idUsuarioCulpado = isset($body['idUsuarioCulpado']) ? (int) $body['idUsuarioCulpado'] : 0;
$idReclamador     = (int) $_SESSION['idusuario'];

if (strlen($motivo) < 10) {
    echo json_encode(['erro' => 'O motivo deve ter pelo menos 10 caracteres.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($idUsuarioCulpado <= 0) {
    echo json_encode(['erro' => 'Doador não identificado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmtR = $pdo->prepare("SELECT NOME FROM Usuario WHERE IDUSUARIO = :id");
    $stmtR->execute([':id' => $idReclamador]);
    $reclamador = $stmtR->fetch(PDO::FETCH_ASSOC);

    // Verifica se o culpado existe
    $stmtD = $pdo->prepare("SELECT NOME FROM Usuario WHERE IDUSUARIO = :id");
    $stmtD->execute([':id' => $idUsuarioCulpado]);
    $denunciado = $stmtD->fetch(PDO::FETCH_ASSOC);

    if (!$reclamador || !$denunciado) {
        echo json_encode(['erro' => 'Usuário não encontrado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO Denuncia (DIA_HORA, MOTIVO, IDRECLAMADOR, IDDENUNCIADO)
        VALUES (NOW(), :motivo, :reclamador, :denunciado)
    ");
    $stmt->execute([
        ':motivo'     => $motivo,
        ':reclamador' => $idReclamador,
        ':denunciado' => $idUsuarioCulpado,
    ]);

    echo json_encode([
        'sucesso'    => true,
        'idDenuncia' => $pdo->lastInsertId(),
        'reclamador' => $reclamador['NOME'],
        'denunciado' => $denunciado['NOME']
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}