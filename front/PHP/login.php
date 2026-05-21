<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'conexaoBD.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

// ── 1. Coleta dos campos ──────────────────────────────────────────────────────
$email         = trim($_POST['email']        ?? '');
$identificacao = trim($_POST['identificacao'] ?? '');
$senha         = $_POST['senha']             ?? '';
$tipoUsuario   = trim($_POST['tipoUsuario']  ?? '');

// ── 2. Validações básicas ─────────────────────────────────────────────────────
$erros = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
if ($identificacao === '') $erros[] = 'CPF ou CNPJ é obrigatório.';
if ($senha === '')         $erros[] = 'Senha é obrigatória.';

if (!empty($erros)) {
    $mensagem = implode('<br>', $erros);
    echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'>
          <link rel='stylesheet' href='../CSS/login.css'></head><body>
          <p style='color:red;padding:20px'>$mensagem</p>
          <a href='../login.html'>← Voltar</a></body></html>";
    exit;
}

// ── 3. Busca o usuário no banco ───────────────────────────────────────────────
try {

    $stmt = $pdo->prepare("
        SELECT u.IDUSUARIO, u.NOME, u.SENHA, p.NOME AS PERMISSAO
        FROM Usuario u
        INNER JOIN Permissao p ON p.IDPERMISSAO = u.IDPERMISSAO
        WHERE u.EMAIL = :email
          AND u.IDENTIFICACAO = :ident
        LIMIT 1
    ");
    $stmt->execute([
        ':email' => $email,
        ':ident' => $identificacao,
    ]);
    $usuario = $stmt->fetch();

    // Verifica se o usuário existe e se a senha está correta
    if (!$usuario || !password_verify($senha, $usuario['SENHA'])) {
        echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'>
              <link rel='stylesheet' href='../CSS/login.css'></head><body>
              <p style='color:red;padding:20px'>E-mail, CPF/CNPJ ou senha incorretos.</p>
              <a href='../login.html'>← Voltar</a></body></html>";
        exit;
    }

    // Verifica se o tipo de usuário selecionado bate com o do banco
    if (strtolower($usuario['PERMISSAO']) !== strtolower($tipoUsuario)) {
        echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'>
              <link rel='stylesheet' href='../CSS/login.css'></head><body>
              <p style='color:red;padding:20px'>Tipo de usuário incorreto para esta conta.</p>
              <a href='../login.html'>← Voltar</a></body></html>";
        exit;
    }

    // ── 4. Inicia sessão ──────────────────────────────────────────────────────
    $_SESSION['idusuario'] = $usuario['IDUSUARIO'];
    $_SESSION['nome']      = $usuario['NOME'];
    $_SESSION['permissao'] = $usuario['PERMISSAO'];

    // ── 5. Redireciona conforme a permissão ───────────────────────────────────
    $permissao = strtolower($usuario['PERMISSAO']);

    if ($permissao === 'doador') {
        header('Location: /divulgaxampp/front/doadorLogado.html');
    } elseif ($permissao === 'administrador') {
        header('Location: /divulgaxampp/front/adminLogado.html');
    } elseif ($permissao === 'recebedor') {
        header('Location: /divulgaxampp/front/recebedorLogado.html');
    } else {
        header('Location: /divulgaxampp/front/telaInicial.html');
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erro no login: " . htmlspecialchars($e->getMessage());
}
