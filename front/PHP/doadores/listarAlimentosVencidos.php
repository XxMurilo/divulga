<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(
        ['erro' => true, 'mensagem' => 'Não autorizado. Faça login para continuar.'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

try {
    // Verifica se a coluna IDSTATUS já existe na tabela
    $colExists = $pdo->query("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'Alimento_doador'
          AND COLUMN_NAME  = 'IDSTATUS'
    ")->fetchColumn();

    if ($colExists) {
        // Atualiza status para Vencido dos que passaram da validade
        $stmtAtualizar = $pdo->prepare("
            UPDATE Alimento_doador
            SET    IDSTATUS = (SELECT IDSTATUS FROM Status WHERE NOME = 'Vencido' LIMIT 1)
            WHERE  IDUSUARIO = :idusuario
              AND  VALIDADE  < CURDATE()
              AND  IDSTATUS != (SELECT IDSTATUS FROM Status WHERE NOME = 'Vencido' LIMIT 1)
        ");
        $stmtAtualizar->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
        $stmtAtualizar->execute();

        $sql = "
            SELECT
                ad.IDALIMENTO_DOADOR  AS id,
                a.NOME                AS nome,
                t.IDTIPO              AS tipo_id,
                t.NOME                AS tipo,
                ad.QUANTIDADE         AS quantidade,
                ad.VALIDADE           AS validade,
                ad.DESCRICAO          AS descricao
            FROM  Alimento_doador ad
            JOIN  Alimento  a ON ad.IDALIMENTO = a.IDALIMENTO
            JOIN  Tipo      t ON a.IDTIPO      = t.IDTIPO
            WHERE ad.IDUSUARIO = :idusuario
              AND ad.IDSTATUS  = (
                  SELECT IDSTATUS FROM Status WHERE NOME = 'Vencido' LIMIT 1
              )
            ORDER BY ad.VALIDADE DESC
        ";
    } else {
        // Fallback: usa VALIDADE diretamente (antes da migração)
        $sql = "
            SELECT
                ad.IDALIMENTO_DOADOR  AS id,
                a.NOME                AS nome,
                t.IDTIPO              AS tipo_id,
                t.NOME                AS tipo,
                ad.QUANTIDADE         AS quantidade,
                ad.VALIDADE           AS validade,
                ad.DESCRICAO          AS descricao
            FROM  Alimento_doador ad
            JOIN  Alimento  a ON ad.IDALIMENTO = a.IDALIMENTO
            JOIN  Tipo      t ON a.IDTIPO      = t.IDTIPO
            WHERE ad.IDUSUARIO = :idusuario
              AND ad.VALIDADE  < CURDATE()
            ORDER BY ad.VALIDADE DESC
        ";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(
        ['erro' => false, 'alimentos' => $stmt->fetchAll()],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(
        ['erro' => true, 'mensagem' => $e->getMessage()],
        JSON_UNESCAPED_UNICODE
    );
}
