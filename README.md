# Seeds of Good

Sistema web de doação de alimentos desenvolvido como Trabalho de Conclusão de Curso (TCC) da ETEC.

---

## Sobre o Projeto

O Seeds of Good é uma plataforma desenvolvida com o objetivo de reduzir o desperdício alimentar por meio da conexão entre doadores e recebedores de alimentos.

O sistema permite que empresas e pessoas físicas disponibilizem alimentos excedentes para doação, enquanto usuários interessados podem visualizar, reservar e retirar esses produtos de forma organizada e segura.

Além disso, a plataforma conta com um módulo administrativo responsável pelo gerenciamento de usuários, denúncias e monitoramento geral das atividades.

---

## Justificativa

O desperdício de alimentos representa um dos principais desafios sociais, econômicos e ambientais da atualidade.

Diversos estabelecimentos descartam diariamente produtos ainda próprios para consumo devido à proximidade da data de vencimento ou excesso de estoque, enquanto grande parte da população enfrenta dificuldades de acesso à alimentação.

Diante desse cenário, o Seeds of Good busca contribuir para a redução desses impactos através da tecnologia, promovendo uma ponte entre quem possui alimentos disponíveis e quem necessita deles.

---

## Objetivos

### Objetivo Geral

Desenvolver uma aplicação web para gerenciamento e intermediação de doações de alimentos.

### Objetivos Específicos

- Facilitar o cadastro de doadores e recebedores;
- Permitir o gerenciamento de alimentos disponíveis para doação;
- Controlar reservas de alimentos;
- Disponibilizar ferramentas administrativas para supervisão do sistema;
- Promover o combate ao desperdício alimentar.

---

## Funcionalidades

### Doador
- Cadastro de alimentos para doação (com foto, validade e quantidade);
- Alteração e exclusão de alimentos cadastrados;
- Visualização e atualização de status das reservas;
- Gerenciamento da própria conta;
- Registro de denúncias.

### Recebedor
- Visualização e pesquisa dos alimentos disponíveis;
- Criação e cancelamento de reservas;
- Registro de denúncias.

### Administrador
- Gerenciamento e ativação/inativação de usuários;
- Consulta de denúncias e alimentos cadastrados;
- Monitoramento geral do sistema.

---

## Tecnologias Utilizadas

| Camada | Tecnologia |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| Backend | PHP 8+ |
| Banco de dados | MySQL via PDO |
| Servidor local | XAMPP (Apache + MySQL) |
| Fontes | Inter (Google Fonts), Material Symbols Outlined |

---

## Como Rodar Localmente

### Pré-requisitos
- [XAMPP](https://www.apachefriends.org/) instalado
- Apache e MySQL iniciados no painel do XAMPP

### Passo a passo

**1. Copiar o projeto para o XAMPP**
```
C:\xampp\htdocs\divulga\
```

**2. Criar o banco de dados**

Acesse `http://localhost/phpmyadmin`, clique em **SQL** e cole o conteúdo do arquivo:
```
Banco_de_Dados/dorminhoco.sql
```
Isso cria o banco `tcc_alimento` com todas as tabelas e dados de exemplo.

**3. Acessar o sistema**
```
http://localhost/divulga/
```

### Usuários de teste

| Nome | E-mail | Senha | Perfil |
|---|---|---|---|
| Carlos Henrique | carlos@gmail.com | 123456 | Doador |
| Mariana Souza | mariana@gmail.com | 654321 | Recebedor |
| João Pedro | joao@gmail.com | admin123 | Administrador |

> ⚠️ **Atenção:** As senhas no SQL estão em texto puro, mas o sistema usa `password_verify()` (hash bcrypt).
> Para que o login funcione, atualize as senhas no banco via phpMyAdmin:
> ```sql
> UPDATE Usuario SET SENHA = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE EMAIL = 'joao@gmail.com';
> ```
> Esse hash corresponde à senha `password`. Para usar sua própria senha, crie um arquivo PHP temporário e execute:
> ```php
> <?php echo password_hash('suasenha', PASSWORD_DEFAULT); ?>
> ```

---

## Estrutura de Pastas

```
divulga/
│
├── index.html                    # Tela inicial (landing page com scroll snap)
├── login.html                    # Tela de login (todos os perfis)
├── cadastro.html                 # Tela de cadastro de novos usuários
│
├── assets/
│   ├── css/
│   │   ├── style.css             # Estilos da tela inicial
│   │   ├── login.css             # Estilos da tela de login
│   │   ├── cadastro.css          # Estilos do cadastro
│   │   ├── doadorLogado.css      # Painel do doador
│   │   ├── adminLogado.css       # Painel do administrador
│   │   ├── recebedor.css         # Painel do recebedor
│   │   └── termos.css            # Página de termos de uso
│   │
│   ├── js/
│   │   ├── main.js               # Animações e scroll da tela inicial
│   │   ├── login.js              # Mensagem de erro e olho da senha
│   │   ├── cadastro.js           # Validação do formulário de cadastro
│   │   ├── doador.js             # Lógica do painel do doador
│   │   ├── recebedor.js          # Lógica do painel do recebedor
│   │   └── admin.js              # Lógica do painel do administrador
│   │
│   └── img/
│       ├── LogoTCC_Transparente.png
│       ├── trigo.svg             # Ícone decorativo (espiga de trigo)
│       ├── maca.svg              # Ícone decorativo (maçã)
│       └── folha.svg             # Ícone decorativo (folha)
│
├── backend/
│   ├── conexaoBD.php             # Conexão com o banco de dados (PDO)
│   ├── login.php                 # Autenticação e redirecionamento por perfil
│   ├── logout.php                # Encerra a sessão do usuário
│   ├── cadastro.php              # Cadastra novo usuário com senha hash
│   │
│   ├── doadores/
│   │   ├── inserirAlimentos.php
│   │   ├── listarAlimentos.php
│   │   ├── alterarAlimentos.php
│   │   ├── excluirAlimentos.php
│   │   ├── listarReservas.php
│   │   ├── atualizarReserva.php
│   │   ├── listarAlimentosVencidos.php
│   │   ├── pesquisarAlimentos.php
│   │   ├── minhaConta.php
│   │   ├── atualizarConta.php
│   │   └── criarDenuncia.php
│   │
│   ├── recebedor/
│   │   ├── listarAlimentos.php
│   │   ├── criarReserva.php
│   │   ├── listarReservas.php
│   │   ├── cancelarReserva.php
│   │   ├── minhaConta.php
│   │   ├── atualizarConta.php
│   │   └── criarDenuncia.php
│   │
│   └── administradores/
│       ├── adminTables.php
│       ├── listCondicoes.php
│       └── atualizarCondicaoUsuario.php
│
├── front/
│   ├── doadorLogado.html         # Painel do doador (pós-login)
│   ├── recebedorLogado.html      # Painel do recebedor (pós-login)
│   ├── adminLogado.html          # Painel do administrador (pós-login)
│   └── termos.html               # Termos de uso e responsabilidades
│
└── Banco_de_Dados/
    ├── dorminhoco.sql            # Script completo (cria tabelas + insere dados)
    └── AdminView.sql             # View auxiliar usada pelo painel admin
```

---

## Banco de Dados

### Diagrama das tabelas

```
Estado
  └── Cidade
        └── Usuario ──── Permissao
              │     └─── Condicao
              │
        ┌─────┴──────────────┐
     Denuncia          Alimento_doador ──── Alimento ──── Tipo
                             │
                          Reserva ──── Status
```

### Descrição das tabelas

| Tabela | Descrição |
|---|---|
| `Usuario` | Todos os usuários (doadores, recebedores, admins) |
| `Permissao` | Perfis: `Doador` (1), `Recebedor` (2), `Administrador` (3) |
| `Condicao` | Situação da conta: `Ativo` (1), `Inativo` (2), `Pendente` (3) |
| `Alimento_doador` | Alimentos publicados pelos doadores |
| `Alimento` | Catálogo de tipos de alimento (Arroz, Alface, etc.) |
| `Tipo` | Categoria do alimento (Verduras, Grãos, Frutas, etc.) |
| `Reserva` | Reservas feitas por recebedores |
| `Status` | Status das reservas: Disponível, Reservado, Cancelado, Entregue, Vencido |
| `Denuncia` | Denúncias registradas entre usuários |
| `Cidade` / `Estado` | Localização dos usuários |

---

## Arquivos de Código Importantes

### `backend/conexaoBD.php`

Realiza a conexão com o MySQL usando **PDO**. Todos os arquivos PHP do backend incluem este arquivo com `require_once 'conexaoBD.php'`.

```php
$pdo = new PDO("mysql:host=localhost;dbname=tcc_alimento", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   // lança exceções em erros
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // retorna array associativo
```

---

### `backend/login.php`

Recebe o formulário de login via POST, valida a senha com **bcrypt** e redireciona o usuário para o painel correto conforme seu perfil.

```php
// 1. Busca o usuário e o nome da permissão com JOIN
SELECT u.IDUSUARIO, u.NOME, u.SENHA, p.NOME AS PERMISSAO
FROM Usuario u
INNER JOIN Permissao p ON p.IDPERMISSAO = u.IDPERMISSAO
WHERE u.EMAIL = :email

// 2. Verifica a senha (nunca comparar texto puro — sempre usar password_verify)
if (!password_verify($senha, $usuario['SENHA'])) {
    header('Location: ../login.html?erro=credenciais');
    exit;
}

// 3. Salva dados na sessão
$_SESSION['idusuario'] = $usuario['IDUSUARIO'];
$_SESSION['nome']      = $usuario['NOME'];
$_SESSION['permissao'] = $usuario['PERMISSAO'];

// 4. Redireciona conforme o perfil
doador        → front/doadorLogado.html
administrador → front/adminLogado.html
recebedor     → front/recebedorLogado.html
```

Erros retornam para `login.html` com parâmetro na URL:
- `?erro=credenciais` — e-mail ou senha incorretos
- `?erro=campos` — campos não preenchidos corretamente

---

### `backend/cadastro.php`

Cadastra um novo usuário. Pontos importantes:
- Usa `password_hash($senha, PASSWORD_DEFAULT)` para **nunca salvar a senha em texto puro**
- Verifica se o e-mail já existe antes de inserir
- Define `IDCONDICAO = 3` (Pendente) — o administrador precisa ativar a conta manualmente

---

### `assets/js/login.js`

Lê o parâmetro `?erro=` da URL e exibe a mensagem de erro dentro do card de login com animação. Após exibir, limpa o parâmetro da URL sem recarregar a página:

```js
const erro = new URLSearchParams(window.location.search).get('erro');
if (erro) {
    document.getElementById('msgErro').style.display = 'flex';
    // ...
    history.replaceState(null, '', window.location.pathname); // remove ?erro= da URL
}
```

---

### `assets/js/main.js`

Scripts da tela inicial (`index.html`):

| Função | O que faz |
|---|---|
| Contador animado | Anima os números (30%, 46 mi, 100%) da stat-faixa ao carregar |
| Scroll snap + dots | Detecta a seção visível e ativa o dot de navegação correspondente |
| IntersectionObserver | Aplica a classe `.visivel` nos cards ao entrar na viewport (fade-in) |
| Botão voltar ao topo | Aparece após rolar e volta ao topo ao clicar |

---

### `assets/css/style.css`

CSS da tela inicial. Define a paleta de cores como **variáveis CSS** reutilizadas em todos os outros arquivos:

```css
:root {
    --verde-escuro:  #1b4332;   /* fundo do header e hero */
    --verde-medio:   #2d6a4f;   /* botões e destaques */
    --verde-vivo:    #40916c;   /* cor principal de ação */
    --verde-claro:   #74c69d;   /* acentos e bordas */
    --verde-palido:  #d8f3dc;   /* fundos suaves */
    --off-white:     #faf9f6;   /* fundo dos cards */
    --cinza-fundo:   #f4f9f6;   /* fundo das seções */
}
```

A tela usa **CSS Scroll Snap** para dividir o conteúdo em 4 janelas de tela cheia com rolagem travada:

```css
.container-snap {
    height: calc(100vh - 80px);
    overflow-y: auto;
    scroll-snap-type: y mandatory; /* trava o scroll em cada seção */
}

.janela-contexto {
    height: calc(100vh - 80px);
    scroll-snap-align: start;     /* cada seção ocupa exatamente uma tela */
}
```

---

## Arquitetura do Sistema

```
Cliente (HTML + CSS + JavaScript)
            │
            │  fetch() / formulários POST
            ▼
     Servidor PHP (backend/)
            │
            │  PDO (prepared statements)
            ▼
     Banco de Dados MySQL
        (tcc_alimento)
```

O frontend se comunica com o backend exclusivamente via:
- **Formulários HTML** com `method="POST"` (login, cadastro)
- **`fetch()` assíncrono** nos painéis (carregar listas, salvar dados)

---

## Contato

**Murillo Morgon** — murillomorgon@gmail.com — +55 (16) 99700-7479

Foro: Ribeirão Preto — SP
