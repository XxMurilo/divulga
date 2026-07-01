<?php
// listarReservas.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

function normalizarImagem($url) {
    if (empty($url)) return null;
    $url = str_replace('\\', '/', $url);
    if (preg_match('#uploads/alimentos/[^/]+$#', $url, $m)) {
        // Caminho relativo à pasta front/, de onde as páginas logadas fazem a requisição
        return '../' . $m[0];
    }
    return null;
}

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
            r.IDSTATUS               AS idStatus,
            r.QUANTIDADE_RESERVADA   AS quantidadeReservada,
            a.NOME                   AS nomeAlimento,
            ad.VALIDADE              AS validade,
            ad.IMAGEM_URL            AS imagem,
            u.IDUSUARIO              AS idDoador,
            u.NOME                   AS nomeDoador,
            u.ENDERECO               AS enderecoDoador,
            s.NOME                   AS statusReserva
        FROM  Reserva r
        INNER JOIN Alimento_doador ad ON ad.IDALIMENTO_DOADOR = r.IDALIMENTO_DOADOR
        INNER JOIN Alimento        a  ON a.IDALIMENTO         = ad.IDALIMENTO
        INNER JOIN Usuario         u  ON u.IDUSUARIO          = ad.IDUSUARIO
        INNER JOIN Status          s  ON s.IDSTATUS           = r.IDSTATUS
        WHERE r.IDUSUARIO = :idusuario
        ORDER BY r.IDRESERVA DESC
    ");
    $stmt->execute([':idusuario' => $idUsuario]);
    $reservas = $stmt->fetchAll();
    foreach ($reservas as &$row) {
        $row['imagem'] = normalizarImagem($row['imagem'] ?? null);
    }
    unset($row);

    echo json_encode(['sucesso' => true, 'reservas' => $reservas], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
