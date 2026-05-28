async function carregarTabela(param, texto = '') {

    const resposta = await fetch(
        `PHP/administradores/adminTables.php?table=${encodeURIComponent(param)}&texto=${encodeURIComponent(texto)}`
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
    const colunas = Object.keys(dados[0]);
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
                                <button onclick="abrirModalCondicao(${linha.id})">Editar</button>
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
                <select id="userConditions"><option value="">Selecione uma Condição</option></select>
                <button id="btnAcaoModal">Salvar</button>
            </div>
        </div>
    `;

    // 2. Injeta o modal no final do body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // 3. Seleciona os elementos que acabaram de ser criados para dar a funcionalidade de fechar
    const modalElemento = document.getElementById('modalCondicao');
    const botaoFechar = document.getElementById('btnFecharModal');
    const botaoAcao = document.getElementById('btnAcaoModal');

    // Função interna para destruir o modal do HTML
    function fecharModal() {
        modalElemento.remove(); 
    }

    // Fecha ao clicar no 'X' ou no botão 'Entendido'
    botaoFechar.addEventListener('click', fecharModal);

    // Fecha se o usuário clicar no fundo escuro (fora da caixinha branca)
    modalElemento.addEventListener('click', (evento) => {
        if (evento.target === modalElemento) {
            fecharModal();
        }
    });

    fetch("PHP/administradores/listCondicoes.php")
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
        console.error = ("Erro: ", erro);
        msg.innerText = "Erro ao carregar condições.";
    });
}