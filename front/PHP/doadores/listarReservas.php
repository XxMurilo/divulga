<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => true, 'mensagem' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Lista reservas feitas em alimentos do doador logado
    $sql = "SELECT r.IDRESERVA        AS idreserva,
                   r.QUANTIDADE_RESERVADA AS quantidade_reservada,
                   s.NOME             AS status,
                   a.NOME             AS alimento_nome,
                   u.NOME             AS recebedor_nome,
                   u.EMAIL            AS recebedor_email,
                   u.TELEFONE         AS recebedor_telefone
            FROM Reserva r
            JOIN Alimento_doador ad ON ad.IDALIMENTO_DOADOR = r.IDALIMENTO_DOADOR
            JOIN Alimento a  ON a.IDALIMENTO  = ad.IDALIMENTO
            JOIN Status   s  ON s.IDSTATUS    = r.IDSTATUS
            JOIN Usuario  u  ON u.IDUSUARIO   = r.IDUSUARIO
            WHERE ad.IDUSUARIO = :idusuario
            ORDER BY r.IDRESERVA DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(
        ['erro' => false, 'reservas' => $stmt->fetchAll()],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => true, 'mensagem' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
