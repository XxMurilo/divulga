async function carregarTabela(param) {
    const resposta = await fetch(
        `../PHP/adminTables.php?table=${encodeURIComponent(param)}`
    );

    const dados = await resposta.text();

    document.getElementById(TableView).innerHTML = '';
}