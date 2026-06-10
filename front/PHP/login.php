<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'conexaoBD.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha']     ?? '';

$erros = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
if ($senha === '') $erros[] = 'Senha é obrigatória.';

if (!empty($erros)) {
    $mensagem = implode('<br>', $erros);
    echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'>
          <link rel='stylesheet' href='../CSS/login.css'></head><body>
          <p style='color:red;padding:20px'>$mensagem</p>
          <a href='../login.html'>← Voltar</a></body></html>";
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT u.IDUSUARIO, u.NOME, u.SENHA, p.NOME AS PERMISSAO
        FROM Usuario u
        INNER JOIN Permissao p ON p.IDPERMISSAO = u.IDPERMISSAO
        WHERE u.EMAIL = :email
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($senha, $usuario['SENHA'])) {
        echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'>
              <link rel='stylesheet' href='../CSS/login.css'></head><body>
              <p style='color:red;padding:20px'>E-mail ou senha incorretos.</p>
              <a href='../login.html'>← Voltar</a></body></html>";
        exit;
    }

    $_SESSION['idusuario'] = $usuario['IDUSUARIO'];
    $_SESSION['nome']      = $usuario['NOME'];
    $_SESSION['permissao'] = $usuario['PERMISSAO'];

    $permissao = strtolower($usuario['PERMISSAO']);

    if ($permissao === 'doador') {
        header('Location: /divulga/front/doadorLogado.html');
    } elseif ($permissao === 'administrador') {
        header('Location: /divulga/front/adminLogado.html');
    } elseif ($permissao === 'recebedor') {
        header('Location: /divulga/front/recebedorLogado.html');
    } else {
        header('Location: /divulga/front/telaInicial.html');
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erro no login: " . htmlspecialchars($e->getMessage());
}