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

    // 3. Constrói e injeta a tabela dinamicamente
    document.getElementById('TableView').innerHTML = `
        <table border="1" id="admView" style="border-collapse: collapse; width: 100%; text-align: left;">
            <thead>
                <tr>
                    <!-- Cria os cabeçalhos (TH) dinamicamente -->
                    ${colunas.map(coluna => `<th>${coluna.toUpperCase()}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                <!-- Cria as linhas (TR) e células (TD) dinamicamente -->
                ${dados.map(linha => `
                    <tr>
                        ${colunas.map(coluna => `<td>${linha[coluna]}</td>`).join('')}
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