<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {

    http_response_code(401);

    echo json_encode([
        'erro' => true,
        'mensagem' => 'Usuário não autenticado.'
    ]);

    exit;
}

try {

    $nome = trim($_POST['nome'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');

    $quantidade = trim($_POST['quantidade'] ?? '');
    $validade = trim($_POST['validade'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    $idusuario = $_SESSION['idusuario'];

    // ── UPLOAD DE FOTO ──
    // __DIR__ = front/PHP/doadores  →  ../../  = front/
    $imagem_url = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($extensao, $extensoesPermitidas)) {
            echo json_encode(['erro' => true, 'mensagem' => 'Formato de imagem inválido. Use JPG, PNG, GIF ou WEBP.']);
            exit;
        }

        // Pasta física: front/uploads/alimentos/
        $pastaUploads = realpath(__DIR__ . '/../../') . '/uploads/alimentos/';
        if (!is_dir($pastaUploads)) {
            mkdir($pastaUploads, 0755, true);
        }

        $nomeArquivo = uniqid('alimento_', true) . '.' . $extensao;
        $destino = $pastaUploads . $nomeArquivo;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
            // Caminho relativo a front/ — usado como src no HTML
            $imagem_url = 'uploads/alimentos/' . $nomeArquivo;
        }
    }

    // ── VERIFICA SE ALIMENTO JÁ EXISTE ──

    $sql = "SELECT IDALIMENTO
            FROM Alimento
            WHERE NOME = :nome
            AND IDTIPO = :tipo";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nome' => $nome,
        ':tipo' => $tipo
    ]);

    $alimento = $stmt->fetch();

    // ── SE NÃO EXISTIR, CRIA ──

    if (!$alimento) {

        $sql = "INSERT INTO Alimento
                (NOME, IDTIPO)
                VALUES
                (:nome, :tipo)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nome' => $nome,
            ':tipo' => $tipo
        ]);

        $idalimento = $pdo->lastInsertId();

    } else {

        $idalimento = $alimento['IDALIMENTO'];
    }

    // ── INSERE DOAÇÃO ──

    $sql = "INSERT INTO Alimento_doador
            (
                VALIDADE,
                QUANTIDADE,
                DESCRICAO,
                IMAGEM_URL,
                IDUSUARIO,
                IDALIMENTO
            )
            VALUES
            (
                :validade,
                :quantidade,
                :descricao,
                :imagem_url,
                :idusuario,
                :idalimento
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':validade'   => $validade,
        ':quantidade' => $quantidade,
        ':descricao'  => $descricao,
        ':imagem_url' => $imagem_url,
        ':idusuario'  => $idusuario,
        ':idalimento' => $idalimento
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Alimento cadastrado com sucesso.'
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'erro' => true,
        'mensagem' => $e->getMessage()
    ]);
}
