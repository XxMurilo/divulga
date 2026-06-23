<?php
// criarReserva.php
// POST body JSON: { idAlimentoDoador: int, quantidade: int }
// Verifica se alimento não está VENCIDO antes de reservar.

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$body             = json_decode(file_get_contents('php://input'), true);
$idAlimentoDoador = isset($body['idAlimentoDoador']) ? (int) $body['idAlimentoDoador'] : 0;
$quantidade       = isset($body['quantidade'])        ? (int) $body['quantidade']        : 0;
$idUsuario        = (int) $_SESSION['idusuario'];

if ($idAlimentoDoador <= 0 || $quantidade <= 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verifica se a coluna IDSTATUS já existe na tabela
    $colExists = $pdo->query("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'Alimento_doador'
          AND COLUMN_NAME  = 'IDSTATUS'
    ")->fetchColumn();

    if ($colExists) {
        // Busca o alimento com status e trava a linha
        $stmt = $pdo->prepare("
            SELECT ad.IDALIMENTO_DOADOR,
                   ad.QUANTIDADE,
                   s.NOME AS status_nome
            FROM   Alimento_doador ad
            JOIN   Status s ON s.IDSTATUS = ad.IDSTATUS
            WHERE  ad.IDALIMENTO_DOADOR = :id
            FOR UPDATE
        ");
        $stmt->execute([':id' => $idAlimentoDoador]);
        $alimento = $stmt->fetch();

        if (!$alimento) {
            $pdo->rollBack();
            echo json_encode(['erro' => 'Alimento não encontrado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Bloqueia reserva de alimento vencido
        if (strtolower($alimento['status_nome']) === 'vencido') {
            $pdo->rollBack();
            http_response_code(422);
            echo json_encode(
                ['erro' => 'Este alimento está vencido e não pode ser reservado.'],
                JSON_UNESCAPED_UNICODE
            );
            exit;
        }
    } else {
        // Fallback: verifica pela VALIDADE diretamente
        $stmt = $pdo->prepare("
            SELECT IDALIMENTO_DOADOR, QUANTIDADE, VALIDADE
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

        // Bloqueia reserva de alimento vencido pela data
        if ($alimento['VALIDADE'] < date('Y-m-d')) {
            $pdo->rollBack();
            http_response_code(422);
            echo json_encode(
                ['erro' => 'Este alimento está vencido e não pode ser reservado.'],
                JSON_UNESCAPED_UNICODE
            );
            exit;
        }
    }

    // Verifica quantidade disponível
    if ($alimento['QUANTIDADE'] < $quantidade) {
        $pdo->rollBack();
        echo json_encode(
            ['erro' => "Quantidade insuficiente. Disponível: {$alimento['QUANTIDADE']}."],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    // Decrementa a quantidade do alimento
    $stmtUpd = $pdo->prepare("
        UPDATE Alimento_doador
        SET    QUANTIDADE = QUANTIDADE - :qtd
        WHERE  IDALIMENTO_DOADOR = :id
    ");
    $stmtUpd->execute([':qtd' => $quantidade, ':id' => $idAlimentoDoador]);

    // Cria a reserva (IDSTATUS = 2 = Reservado)
    $stmtRes = $pdo->prepare("
        INSERT INTO Reserva (QUANTIDADE_RESERVADA, IDSTATUS, IDUSUARIO, IDALIMENTO_DOADOR)
        VALUES (:qtd, 2, :idusuario, :idAlimentoDoador)
    ");
    $stmtRes->execute([
        ':qtd'              => $quantidade,
        ':idusuario'        => $idUsuario,
        ':idAlimentoDoador' => $idAlimentoDoador,
    ]);

    $pdo->commit();
    echo json_encode(['sucesso' => true, 'idReserva' => $pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
