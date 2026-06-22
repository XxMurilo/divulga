<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../conexaoBD.php';

if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => true, 'mensagem' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Garante que somente doadores acessam este endpoint
if (strtolower($_SESSION['permissao'] ?? '') !== 'doador') {
    http_response_code(403);
    echo json_encode(['erro' => true, 'mensagem' => 'Acesso negado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => true, 'mensagem' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$nome     = trim($_POST['nome']     ?? '');
$email    = trim($_POST['email']    ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$senha    = $_POST['senha']         ?? '';

$erros = [];
if ($nome === '')  $erros[] = 'Nome é obrigatório.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
if ($telefone === '') $erros[] = 'Telefone é obrigatório.';
if ($endereco === '') $erros[] = 'Endereço é obrigatório.';
if ($senha !== '' && strlen($senha) < 6) $erros[] = 'A senha deve ter pelo menos 6 caracteres.';

if (!empty($erros)) {
    echo json_encode(['erro' => true, 'mensagem' => implode(' ', $erros)], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $checkEmail = $pdo->prepare(
        "SELECT IDUSUARIO FROM Usuario WHERE EMAIL = :email AND IDUSUARIO != :idusuario LIMIT 1"
    );
    $checkEmail->execute([':email' => $email, ':idusuario' => $_SESSION['idusuario']]);
    if ($checkEmail->fetch()) {
        echo json_encode(['erro' => true, 'mensagem' => 'Este e-mail já está em uso por outra conta.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($senha !== '') {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "UPDATE Usuario SET NOME=:nome, EMAIL=:email, TELEFONE=:tel, ENDERECO=:end, SENHA=:senha
             WHERE IDUSUARIO=:idusuario AND IDPERMISSAO=(SELECT IDPERMISSAO FROM Permissao WHERE NOME='doador' LIMIT 1)"
        );
        $stmt->execute([
            ':nome'      => $nome,
            ':email'     => $email,
            ':tel'       => $telefone,
            ':end'       => $endereco,
            ':senha'     => $senhaHash,
            ':idusuario' => $_SESSION['idusuario'],
        ]);
    } else {
        $stmt = $pdo->prepare(
            "UPDATE Usuario SET NOME=:nome, EMAIL=:email, TELEFONE=:tel, ENDERECO=:end
             WHERE IDUSUARIO=:idusuario AND IDPERMISSAO=(SELECT IDPERMISSAO FROM Permissao WHERE NOME='doador' LIMIT 1)"
        );
        $stmt->execute([
            ':nome'      => $nome,
            ':email'     => $email,
            ':tel'       => $telefone,
            ':end'       => $endereco,
            ':idusuario' => $_SESSION['idusuario'],
        ]);
    }

    $_SESSION['nome'] = $nome;

    echo json_encode(['erro' => false, 'mensagem' => 'Dados atualizados com sucesso!'], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => true, 'mensagem' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
