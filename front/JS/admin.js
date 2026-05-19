async function carregarTabela(param) {
    const resposta = await fetch(
        `PHP/administradores/adminTables.php?table=${encodeURIComponent(param)}`
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
        <table border="1" style="border-collapse: collapse; width: 100%; text-align: left;">
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
    aba.addEventListener("click", () => {

      // remove a seleção dos outros
      abas.forEach(a => a.classList.remove("selecionado"));

      // adiciona no botão clicado
      aba.classList.add("selecionado");
    });
  });