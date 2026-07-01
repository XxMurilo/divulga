<?php
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

try {
    // Verifica se a coluna IDSTATUS já existe na tabela
    $colExists = $pdo->query("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'Alimento_doador'
          AND COLUMN_NAME  = 'IDSTATUS'
    ")->fetchColumn();

    if ($colExists) {
        // Marca vencidos globais antes de retornar a listagem pública
        $pdo->exec("
            UPDATE Alimento_doador
            SET    IDSTATUS = (SELECT IDSTATUS FROM Status WHERE NOME = 'Vencido' LIMIT 1)
            WHERE  VALIDADE  < CURDATE()
              AND  IDSTATUS != (SELECT IDSTATUS FROM Status WHERE NOME = 'Vencido' LIMIT 1)
        ");

        $sql = "
            SELECT
                ad.IDALIMENTO_DOADOR  AS idAlimentoDoador,
                a.NOME                AS nomeAlimento,
                ad.QUANTIDADE         AS quantidade,
                ad.DESCRICAO          AS descricao,
                ad.VALIDADE           AS validade,
                ad.IMAGEM_URL         AS imagem,
                u.NOME                AS nomeDoador,
                u.ENDERECO            AS enderecoDoador
            FROM  Alimento_doador ad
            INNER JOIN Alimento a ON a.IDALIMENTO = ad.IDALIMENTO
            INNER JOIN Usuario  u ON u.IDUSUARIO  = ad.IDUSUARIO
            WHERE ad.QUANTIDADE > 0
              AND ad.IDSTATUS  != (
                  SELECT IDSTATUS FROM Status WHERE NOME = 'Vencido' LIMIT 1
              )
            ORDER BY ad.IDALIMENTO_DOADOR DESC
        ";
    } else {
        // Fallback: filtra por VALIDADE diretamente (antes da migração)
        $sql = "
            SELECT
                ad.IDALIMENTO_DOADOR  AS idAlimentoDoador,
                a.NOME                AS nomeAlimento,
                ad.QUANTIDADE         AS quantidade,
                ad.DESCRICAO          AS descricao,
                ad.VALIDADE           AS validade,
                ad.IMAGEM_URL         AS imagem,
                u.NOME                AS nomeDoador,
                u.ENDERECO            AS enderecoDoador
            FROM  Alimento_doador ad
            INNER JOIN Alimento a ON a.IDALIMENTO = ad.IDALIMENTO
            INNER JOIN Usuario  u ON u.IDUSUARIO  = ad.IDUSUARIO
            WHERE ad.QUANTIDADE > 0
              AND ad.VALIDADE  >= CURDATE()
            ORDER BY ad.IDALIMENTO_DOADOR DESC
        ";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $alimentos = $stmt->fetchAll();
    foreach ($alimentos as &$row) {
        $row['imagem'] = normalizarImagem($row['imagem'] ?? null);
    }
    unset($row);

    echo json_encode(['sucesso' => true, 'alimentos' => $alimentos], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
