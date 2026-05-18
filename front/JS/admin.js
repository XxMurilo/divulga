async function carregarTabela(param) {
    const resposta = await fetch(
        `../PHP/adminTables.php?table=${encodeURIComponent(param)}`
    );

    const dados = await resposta.text();

    // 1. Validação caso a matriz venha vazia
    if (!matrizDados || matrizDados.length === 0) {
        document.getElementById('TableView').innerHTML = "<p>Nenhum dado encontrado.</p>";
        return;
    }

    // 2. Extrai o nome das colunas dinamicamente a partir da primeira linha
    const colunas = Object.keys(matrizDados[0]);

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
                ${matrizDados.map(linha => `
                    <tr>
                        ${colunas.map(coluna => `<td>${linha[coluna]}</td>`).join('')}
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}