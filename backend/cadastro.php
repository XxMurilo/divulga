<?php
// cadastro.php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'conexaoBD.php';
 
// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.html');
    exit;
}
 
// ── 1. Coleta e sanitização básica ──────────────────────────────────────────
$nome           = trim($_POST['nome']          ?? '');
$email          = trim($_POST['email']         ?? '');
$telefone       = trim($_POST['telefone']      ?? '');
$endereco       = trim($_POST['endereco']      ?? '');
$identificacao  = trim($_POST['identificacao'] ?? '');
$identificacao  = preg_replace('/\D/', '', $identificacao);
$senha          = trim($_POST['senha']         ?? '');
$cidade         = trim($_POST['cidade']        ?? '');
$tipoUsuario    = trim($_POST['tipoUsuario']   ?? '');
 
// ── 2. Validações básicas ────────────────────────────────────────────────────
$erros = [];
 
if ($nome === '')          $erros[] = 'Nome é obrigatório.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
if ($telefone === '')      $erros[] = 'Telefone é obrigatório.';
if ($endereco === '')      $erros[] = 'Endereço é obrigatório.';
if ($identificacao === '') $erros[] = 'CPF ou CNPJ é obrigatório.';
if (strlen($senha) < 6)   $erros[] = 'A senha deve ter pelo menos 6 caracteres.';

if (strlen($identificacao) !== 11 && strlen($identificacao) !== 14) {
    $erros[] = 'Esse documento não é CPF nem CNPJ';
}

// se for CNPJ
if (strlen($identificacao) === 14) {

    $url = "https://brasilapi.com.br/api/cnpj/v1/{$identificacao}";

    $contexto = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 10
        ]
    ]);

    $resposta = @file_get_contents($url, false, $contexto);

    if ($resposta === false) {
        $erros[] = 'Erro ao acessar API do CNPJ.';
    } else {
        $empresa = json_decode($resposta, true);

        if (
            !isset($empresa['descricao_situacao_cadastral']) ||
            $empresa['descricao_situacao_cadastral'] !== 'ATIVA'
        ) {
            $erros[] = 'Esta empresa não está ativa.';
        }

        if (isset($empresa['codigo_natureza_juridica'])) {
            $cod = $empresa['codigo_natureza_juridica'];

            if (
                (str_starts_with($cod, '2') && $tipoUsuario === 'Recebedor') ||
                (str_starts_with($cod, '3') && $tipoUsuario === 'Doador')
            ) {
                $erros[] = 'Tipo de Pessoa Jurídica não condizente com a função solicitada';
            }
        }
    }
}

// ── Retorna erro com alert e volta para o formulário ─────────────────────────
if (!empty($erros)) {
    $mensagem = implode('\n', $erros);
    echo "<script>
        alert('$mensagem');
        window.history.back();
    </script>";
    exit;
}
 
// ── 3. Hash da senha ─────────────────────────────────────────────────────────
$senhaHash = password_hash($senha, PASSWORD_BCRYPT);
 
// ── 4. Busca IDs das tabelas auxiliares ──────────────────────────────────────
try {
 
    // --- Permissão (tipo de usuário) ---
    $stmtPerm = $pdo->prepare(
        "SELECT IDPERMISSAO FROM Permissao WHERE NOME = :nome LIMIT 1"
    );
    $stmtPerm->execute([':nome' => $tipoUsuario]);
    $permissao = $stmtPerm->fetch();
 
    if (!$permissao) {
        die("Tipo de usuário '$tipoUsuario' não encontrado na tabela Permissao.");
    }
    $idPermissao = $permissao['IDPERMISSAO'];
 
    // --- Condição padrão ---
    $stmtCond = $pdo->prepare("SELECT IDCONDICAO FROM Condicao LIMIT 1");
    $stmtCond->execute();
    $condicao   = $stmtCond->fetch();
    $idCondicao = $condicao ? $condicao['IDCONDICAO'] : null;
 
    // --- Cidade ---
    $stmtCidade = $pdo->prepare(
        "SELECT IDCIDADE FROM Cidade WHERE NOME = :nome LIMIT 1"
    );
    $stmtCidade->execute([':nome' => $cidade]);
    $cidadeRow = $stmtCidade->fetch();
 
    if (!$cidadeRow) {
        die("Cidade '$cidade' não encontrada na tabela Cidade.");
    }
    $idCidade = $cidadeRow['IDCIDADE'];
 
    // ── 5. Verifica duplicidade de e-mail / identificação ────────────────────
    $stmtDup = $pdo->prepare(
        "SELECT IDUSUARIO FROM Usuario
         WHERE EMAIL = :email OR IDENTIFICACAO = :identificacao
         LIMIT 1"
    );
    $stmtDup->execute([':email' => $email, ':identificacao' => $identificacao]);
 
    if ($stmtDup->fetch()) {
        echo "<script>
            alert('E-mail ou CPF/CNPJ já cadastrado.');
            window.history.back();
        </script>";
        exit;
    }
 
    // ── 6. Insere o usuário ──────────────────────────────────────────────────
    $stmtInsert = $pdo->prepare("
        INSERT INTO Usuario
            (NOME, EMAIL, TELEFONE, ENDERECO, IDENTIFICACAO,
             SENHA, IDPERMISSAO, IDCONDICAO, IDCIDADE)
        VALUES
            (:nome, :email, :telefone, :endereco, :identificacao,
             :senha, :idpermissao, :idcondicao, :idcidade)
    ");
 
    $stmtInsert->execute([
        ':nome'          => $nome,
        ':email'         => $email,
        ':telefone'      => $telefone,
        ':endereco'      => $endereco,
        ':identificacao' => $identificacao,
        ':senha'         => $senha !== '' ? $senhaHash : null,
        ':idpermissao'   => $idPermissao,
        ':idcondicao'    => $idCondicao,
        ':idcidade'      => $idCidade,
    ]);
 
    // ── 7. Inicia sessão e redireciona ───────────────────────────────────────
    $_SESSION['idusuario'] = $pdo->lastInsertId();
    $_SESSION['nome']      = $nome;
    $_SESSION['permissao'] = $tipoUsuario;
 
    if ($tipoUsuario === 'Doador') {
        header('Location: ../doadorLogado.html');
    } elseif ($tipoUsuario === 'Recebedor') {
        header('Location: ../recebedorLogado.html');
    } else {
        header('Location: ../telaInicial.html');
    }
    exit;
 
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erro ao cadastrar: " . htmlspecialchars($e->getMessage());
}