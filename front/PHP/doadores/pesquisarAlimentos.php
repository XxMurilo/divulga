<?php
// pesquisarAlimentos.php
// Retorna os tipos disponíveis no banco (para popular o <select> de filtro/cadastro)
// Também pode ser usado para busca geral de alimentos (tabela Alimento) por nome
// GET ?nome=banana  →  busca na tabela Alimento (catálogo global)
// GET ?tipos=1      →  lista todos os tipos

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(
        [
            'erro' => 'Não autorizado.',
            'mensagem' => 'Faça login para acessar este recurso.'
         ],
         JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT
    );
    exit;
}

// --- Listar tipos ---
if (isset($_GET['tipos'])) {
    try {
        $sql = "SELECT IDTIPO AS id, NOME AS nome FROM Tipo ORDER BY NOME ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        echo json_encode(
            [
             'sucesso' => true,
             'tipos' => $stmt->fetchAll()
             ], 
             JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT
        );

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(
            [
                'erro' => true,
                'mensagem' =>$e->getMessage()
             ], 
             JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    }
    exit;
}


// --- Buscar alimento por nome no catálogo global --
$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';

if ($nome === '') {
    echo json_encode(
        [
            'erro' => true,
            'mensagem' => 'Parâmetro "nome" é obrigatório para busca.'
        ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT);
    exit;
}


// Consulta SQL para buscar alimentos por nome, incluindo o tipo do alimento (para exibir na tabela de doações)
try {
    $sql = "SELECT a.IDALIMENTO AS id, a.NOME AS nome, t.IDTIPO AS tipo_id, t.NOME AS tipo
            FROM Alimento a
            INNER JOIN Tipo t ON t.IDTIPO = a.IDTIPO
            WHERE a.NOME LIKE :nome
            ORDER BY a.NOME ASC
            LIMIT 20";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([':nome' => '%' . $nome . '%']);
    echo json_encode(
        [
         'sucesso' => true,
         'alimentos' => $stmt->fetchAll()
        ], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(
        [
            'erro' => true,
            'mensagem' => $e->getMessage()
        ], 
        JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT
    );
}
