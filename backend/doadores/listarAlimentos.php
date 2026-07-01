<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../conexaoBD.php';

// Normaliza o caminho da imagem para sempre ser relativo a front/
// Aceita: null, '', 'uploads/...', '/uploads/...', caminho absoluto do servidor
function normalizarImagem($url) {
    if (empty($url)) return null;
    // Remove caminho absoluto do servidor, mantém só a parte relativa
    // Ex: /var/www/html/front/uploads/alimentos/x.jpg → uploads/alimentos/x.jpg
    $url = str_replace('\\', '/', $url);
    if (preg_match('#uploads/alimentos/[^/]+$#', $url, $m)) {
        // Caminho relativo à pasta front/, de onde as páginas logadas fazem a requisição
        return '../' . $m[0];
    }
    return null;
}

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(
        ['erro' => 'Não autorizado.', 'mensagem' => 'Faça login para acessar este recurso.'],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
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
        // Marca vencidos do usuário antes de listar (atualização proativa)
        $stmtAtualizar = $pdo->prepare("
            UPDATE Alimento_doador
            SET    IDSTATUS = (SELECT IDSTATUS FROM Status WHERE NOME = 'Vencido' LIMIT 1)
            WHERE  IDUSUARIO = :idusuario
              AND  VALIDADE  < CURDATE()
              AND  IDSTATUS != (SELECT IDSTATUS FROM Status WHERE NOME = 'Vencido' LIMIT 1)
        ");
        $stmtAtualizar->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
        $stmtAtualizar->execute();

        // Lista apenas alimentos NÃO vencidos do doador
        $sql = "SELECT ad.IDALIMENTO_DOADOR AS id,
                       a.NOME               AS nome,
                       t.IDTIPO             AS tipo_id,
                       t.NOME               AS tipo,
                       ad.QUANTIDADE        AS quantidade,
                       ad.VALIDADE          AS validade,
                       ad.DESCRICAO         AS descricao,
                       ad.IMAGEM_URL        AS imagem
                FROM  Alimento_doador ad
                JOIN  Alimento a ON ad.IDALIMENTO = a.IDALIMENTO
                JOIN  Tipo     t ON a.IDTIPO      = t.IDTIPO
                WHERE ad.IDUSUARIO = :idusuario
                  AND ad.IDSTATUS != (
                      SELECT IDSTATUS FROM Status WHERE NOME = 'Vencido' LIMIT 1
                  )
                ORDER BY a.NOME ASC";
    } else {
        // Fallback: usa VALIDADE diretamente (antes da migração)
        $sql = "SELECT ad.IDALIMENTO_DOADOR AS id,
                       a.NOME               AS nome,
                       t.IDTIPO             AS tipo_id,
                       t.NOME               AS tipo,
                       ad.QUANTIDADE        AS quantidade,
                       ad.VALIDADE          AS validade,
                       ad.DESCRICAO         AS descricao,
                       ad.IMAGEM_URL        AS imagem
                FROM  Alimento_doador ad
                JOIN  Alimento a ON ad.IDALIMENTO = a.IDALIMENTO
                JOIN  Tipo     t ON a.IDTIPO      = t.IDTIPO
                WHERE ad.IDUSUARIO = :idusuario
                  AND ad.VALIDADE  >= CURDATE()
                ORDER BY a.NOME ASC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idusuario', $_SESSION['idusuario'], PDO::PARAM_INT);
    $stmt->execute();

    $alimentos = $stmt->fetchAll();

    // Normaliza o caminho da imagem: garante que seja sempre 'uploads/alimentos/arquivo.ext'
    // ou null se não houver foto, independente de como foi salvo no banco
    foreach ($alimentos as &$row) {
        $row['imagem'] = normalizarImagem($row['imagem'] ?? null);
    }
    unset($row);

    echo json_encode(
        ['erro' => false, 'alimentos' => $alimentos],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(
        ['erro' => true, 'mensagem' => $e->getMessage()],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
    exit;
}
