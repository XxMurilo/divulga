async function carregarTabela(param, texto = '') {

    const resposta = await fetch(
        `../backend/administradores/adminTables.php?table=${encodeURIComponent(param)}&texto=${encodeURIComponent(texto)}`
    );

    const dados = await resposta.json();

    // 1. Validação caso a matriz venha vazia
    if (!dados || dados.length === 0) {
        document.getElementById('TableView').innerHTML = "<p>Nenhum dado encontrado.</p>";
        return;
    }

    if (dados.erro) {
    document.getElementById('TableView').innerHTML =
        `<p>${dados.erro}</p>`;
    return;
}

    // 2. Extrai o nome das colunas dinamicamente a partir da primeira linha
    const colunas = Object.keys(dados[0])
    .filter(coluna => coluna !== "IDUSUARIO");
    const isUserTable = param === "user";

    // 3. Constrói e injeta a tabela dinamicamente
    document.getElementById('TableView').innerHTML = `
        <table border="1" id="admView" style="border-collapse: collapse; width: 100%; text-align: left;">
            <thead>
                <tr>
                    <!-- Cria os cabeçalhos (TH) dinamicamente -->
                    ${colunas.map(coluna => `<th>${coluna.toUpperCase()}</th>`).join('')}
                    ${isUserTable ? `<th>AÇÕES</th>` : ''}
                </tr>
            </thead>
            <tbody>
                <!-- Cria as linhas (TR) e células (TD) dinamicamente -->
                ${dados.map(linha => `
                    <tr>
                        ${colunas.map(coluna => `<td>${linha[coluna]}</td>`).join('')}
                        ${isUserTable ? `
                            <td>
                                <button onclick="abrirModalCondicao(${linha.IDUSUARIO})">Editar</button>
                            </td>
                        ` : ''}
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

const abas = document.querySelectorAll(".aba");

abas.forEach(aba => {

    aba.addEventListener("click", async () => {

        abas.forEach(a => a.classList.remove("selecionado"));

        aba.classList.add("selecionado");

        const tabela = aba.dataset.table;

        await carregarTabela(tabela);
    });

});

const barra = document.getElementById('search');

barra.addEventListener('input', () => {
    search();
});

async function search() {

    const texto = barra.value;

    const abaSelecionada = document.querySelector('.aba.selecionado');

    if (!abaSelecionada) {
        return;
    }

    const tabela = abaSelecionada.dataset.table;

    if (texto.trim() === "") {
        await carregarTabela(tabela);
        return;
    }

    await carregarTabela(tabela, texto);
}

function abrirModalCondicao(id) {
    // 1. Define a estrutura HTML do modal em uma string (Template Literal)
    const modalHTML = `
        <div class="modal-overlay" id="modalCondicao">
            <div class="modal-box">
                <button class="modal-close" id="btnFecharModal">&times;</button>
                <h2>Alterar Condição do Usuário</h2>
                <p id="msg"></p>
                <select id="userConditions"><option value="">Selecione uma Condição</option></select>
                <button id="btnAcaoModal">Salvar</button>
            </div>
        </div>
    `;

    // 2. Injeta o modal no final do body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // 3. Seleciona os elementos que acabaram de ser criados 
    const modalElemento = document.getElementById('modalCondicao');
    const botaoFechar = document.getElementById('btnFecharModal');
    const botaoAcao = document.getElementById('btnAcaoModal');
    const msg = document.getElementById('msg');

    // Função interna para destruir o modal do HTML
    function fecharModal() {
        modalElemento.remove(); 
    }

    // Fecha ao clicar no 'X' 
    botaoFechar.addEventListener('click', fecharModal);

    // Fecha se o usuário clicar no fundo escuro (fora da caixinha branca)
    modalElemento.addEventListener('click', (evento) => {
        if (evento.target === modalElemento) {
            fecharModal();
        }
    });

    fetch("../backend/administradores/listCondicoes.php")
    .then(function (resposta) {
        return resposta.json();
    })
    .then(function (condicoes) {
        if(condicoes.erro) {
            msg.innerText = condicoes.mensagem;
            return;
        }
        if(condicoes.length === 0) {
            msg.innerText = "Nenhuma condição cadastrada.";
            return;
        }

        condicoes.forEach(function (condicao) {
            const opcao = document.createElement("option");
            opcao.value = condicao.IDCONDICAO;
            opcao.innerText = condicao.NOME;
            document.getElementById('userConditions').appendChild(opcao);
        });
    })
    .catch(function (erro) {
        console.error("Erro: ", erro);
        msg.innerText = "Erro ao carregar condições.";
    });

    // Chama a função de atualização e fecha o modal
    botaoAcao.addEventListener('click', async function() {
    const nome = document.getElementById('userConditions').value;

    if (nome.trim() !== '') {

        const sucesso = await atualizaCondicao(id, nome);

        if (sucesso) {
            fecharModal();
            await carregarTabela('user'); // 👈 recarrega a tabela
        }

    } else {
        msg.innerText = "Por favor, selecione uma condição antes de salvar.";
    }
});

    const selectCondicoes = document.getElementById('userConditions');

    // Limpa a mensagem de erro assim que o usuário mexe no select para escolher uma opção
    selectCondicoes.addEventListener('change', () => {
    msg.innerText = "";
});
}

async function atualizaCondicao(id, nome) {
    const idCondicao = String(nome).trim();
    const idUsuario = String(id).trim(); 
    const msg = document.getElementById('msg');

    if (idCondicao === "" || idUsuario === "") {
        msg.innerText = "Preencha todos os dados";
        return false;
    }

    try {

        const resposta = await fetch(
            "../backend/administradores/atualizarCondicaoUsuario.php",
            {
                method: "POST",
                body: new URLSearchParams({
                    idCondicao,
                    idUsuario
                })
            }
        );

        const dados = await resposta.json();

        if (dados.erro) {
            msg.innerText = dados.mensagem;
            return false;
        }

        msg.innerText = dados.mensagem;
        return true; 

    } catch (erro) {

        console.log(erro);
        msg.innerText = "Erro ao processar resposta do servidor.";
        return false;
    }
}

// ── Navegação entre seções ─────────────────────────────────────────
const navLinks = document.querySelectorAll('.nav-link');
const secoes   = document.querySelectorAll('.secao');

navLinks.forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const alvo = this.dataset.secao;
        navLinks.forEach(function(l) { l.classList.remove('ativo'); });
        secoes.forEach(function(s)   { s.classList.remove('ativa'); });
        this.classList.add('ativo');
        document.getElementById('secao-' + alvo).classList.add('ativa');

        if (alvo === 'tabelas') ;
        if (alvo === 'conta')    carregarConta();
    });
});

function carregarConta() {
    dadosConta.innerHTML = '<p class="carregando-conta">Carregando dados...</p>';
    fetch('../backend/administradores/minhaConta.php')
        .then(function(r) {
            if (r.status === 401) { window.location.href = 'login.html'; return; }
            return r.json();
        })
        .then(function(dados) {
            if (!dados || dados.erro) {
                dadosConta.innerHTML = '<p>Erro ao carregar dados.</p>';
                return;
            }
            const u = dados.usuario;
            avatarInicial.textContent = u.nome ? u.nome.charAt(0).toUpperCase() : '?';

            dadosConta.innerHTML =
                '<div class="dado-item"><span class="dado-label">Nome</span><span class="dado-valor">'         + esc(u.nome)          + '</span></div>' +
                '<div class="dado-item"><span class="dado-label">E-mail</span><span class="dado-valor">'        + esc(u.email)         + '</span></div>' +
                '<div class="dado-item"><span class="dado-label">Telefone</span><span class="dado-valor">'      + esc(u.telefone)      + '</span></div>' +
                '<div class="dado-item"><span class="dado-label">Endereço</span><span class="dado-valor">'      + esc(u.endereco)      + '</span></div>' +
                '<div class="dado-item"><span class="dado-label">CPF/CNPJ</span><span class="dado-valor">'      + esc(u.identificacao) + '</span></div>';

            // Preenche o formulário de edição
            document.getElementById('editNome').value     = u.nome;
            document.getElementById('editEmail').value    = u.email;
            document.getElementById('editTelefone').value = u.telefone;
            document.getElementById('editEndereco').value = u.endereco;
        })
        .catch(function() { dadosConta.innerHTML = '<p>Erro ao carregar dados.</p>'; });
}

function salvarConta() {
    const senha        = document.getElementById('editSenha').value;
    const senhaConfirm = document.getElementById('editSenhaConfirm').value;

    if (senha && senha !== senhaConfirm) {
        mensagemConta.textContent = 'As senhas não coincidem.';
        mensagemConta.style.color = '#e57373';
        return;
    }

    mensagemConta.textContent = 'Salvando...';
    mensagemConta.style.color = '';

    fetch('../backend/administradores/atualizarConta.php', {
        method: 'POST',
        body: new URLSearchParams({
            nome:     document.getElementById('editNome').value.trim(),
            email:    document.getElementById('editEmail').value.trim(),
            telefone: document.getElementById('editTelefone').value.trim(),
            endereco: document.getElementById('editEndereco').value.trim(),
            senha:    senha
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(dados) {
        mensagemConta.textContent = dados.mensagem;
        mensagemConta.style.color = dados.erro ? '#e57373' : '#2d6a4f';
        if (!dados.erro) {
            document.getElementById('editSenha').value        = '';
            document.getElementById('editSenhaConfirm').value = '';
            cardEdicao.style.display       = 'none';
            cardVisualizacao.style.display = 'block';
            carregarConta();
        }
    })
    .catch(function() {
        mensagemConta.textContent = 'Erro ao salvar dados.';
        mensagemConta.style.color = '#e57373';
    });
}

carregarConta()