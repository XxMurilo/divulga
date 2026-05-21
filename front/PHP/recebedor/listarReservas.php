<?php
// listarReservas.php
// Retorna todas as reservas ativas do recebedor logado

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$idUsuario = (int) $_SESSION['idusuario'];

try {
    $stmt = $pdo->prepare("
        SELECT
            r.IDRESERVA              AS idReserva,
            r.QUANTIDADE_RESERVADA   AS quantidadeReservada,
            a.NOME                   AS nomeAlimento,
            ad.VALIDADE              AS validade,
            u.IDUSUARIO              AS idDoador,
            u.NOME                   AS nomeDoador,
            u.ENDERECO               AS enderecoDoador,
            s.NOME                   AS statusReserva
        FROM  Reserva r
        INNER JOIN Alimento_doador ad ON ad.IDALIMENTO_DOADOR = r.IDALIMENTO_DOADOR
        INNER JOIN Alimento        a  ON a.IDALIMENTO         = ad.IDALIMENTO
        INNER JOIN Usuario         u  ON u.IDUSUARIO          = ad.IDUSUARIO
        LEFT  JOIN Status          s  ON s.IDSTATUS           = r.IDSTATUS
        WHERE r.IDUSUARIO = :idusuario
          AND r.IDSTATUS  = 1
        ORDER BY r.IDRESERVA DESC
    ");
    $stmt->execute([':idusuario' => $idUsuario]);
    $reservas = $stmt->fetchAll();

    echo json_encode(['sucesso' => true, 'reservas' => $reservas], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
