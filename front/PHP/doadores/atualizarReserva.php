<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => true, 'mensagem' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => true, 'mensagem' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$idreserva = (int) ($_POST['idreserva'] ?? 0);
$idstatus  = (int) ($_POST['idstatus']  ?? 0);

if ($idreserva <= 0 || $idstatus <= 0) {
    echo json_encode(['erro' => true, 'mensagem' => 'Dados inválidos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Verifica se a reserva pertence a um alimento do doador logado
    $check = $pdo->prepare(
        "SELECT r.IDRESERVA
         FROM Reserva r
         JOIN Alimento_doador ad ON ad.IDALIMENTO_DOADOR = r.IDALIMENTO_DOADOR
         WHERE r.IDRESERVA = :idreserva AND ad.IDUSUARIO = :idusuario"
    );
    $check->execute([':idreserva' => $idreserva, ':idusuario' => $_SESSION['idusuario']]);

    if (!$check->fetch()) {
        echo json_encode(['erro' => true, 'mensagem' => 'Reserva não encontrada ou sem permissão.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE Reserva SET IDSTATUS = :idstatus WHERE IDRESERVA = :idreserva");
    $stmt->execute([':idstatus' => $idstatus, ':idreserva' => $idreserva]);

    $nomes = [1 => 'Disponível', 2 => 'Reservado', 3 => 'Cancelado'];
    $nomeStatus = $nomes[$idstatus] ?? 'Atualizado';

    echo json_encode(
        ['erro' => false, 'mensagem' => 'Status alterado para "' . $nomeStatus . '" com sucesso.'],
        JSON_UNESCAPED_UNICODE
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => true, 'mensagem' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
