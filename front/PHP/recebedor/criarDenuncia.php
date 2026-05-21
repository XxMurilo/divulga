<?php
// criarDenuncia.php
// POST body JSON: { nome: string, motivo: string }
// Registra uma denúncia associada ao usuário logado.
// O campo "nome" é apenas informativo (vindo do formulário), o IDUSUARIO vem da sessão.

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$nome      = isset($body['nome'])   ? trim($body['nome'])   : '';
$motivo    = isset($body['motivo']) ? trim($body['motivo']) : '';
$idUsuario = (int) $_SESSION['idusuario'];

// Validações
if (strlen($motivo) < 10) {
    echo json_encode(['erro' => 'O motivo deve ter pelo menos 10 caracteres.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($motivo) < 10) {
    echo json_encode(['erro' => 'O motivo deve ter pelo menos 10 caracteres.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO Denuncia (DIA_HORA, MOTIVO, IDUSUARIO)
        VALUES (NOW(), :motivo, :idusuario)
    ");
    $stmt->execute([
        ':motivo'    => $motivo,
        ':idusuario' => $idUsuario,
    ]);

    echo json_encode([
        'sucesso'    => true,
        'idDenuncia' => $pdo->lastInsertId()
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}