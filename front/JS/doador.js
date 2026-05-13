const alimentos = [];
const tiposAlimento = document.getElementById('tipo');
const nome = document.getElementById('nome');
const quantidade = document.getElementById('quantidade');
const descricao = document.getElementById('descricao');

const btnAlterar = document.getElementById('btnAlterar');
btnAlterar.addEventListener('click', alterarAlimento);




function abrirModal() {
    document.getElementById('overlay').classList.add('ativo');
}

function fecharModal() {
    document.getElementById('overlay').classList.remove('ativo');
    limparForm();
}

function limparForm() {
    tiposAlimento.value = '';
    nome.value = '';
    quantidade.value = '';
    descricao.value = '';
}

function salvarAlimento() {
    const tipo = tiposAlimento.value.trim();
    const nome = nome.value.trim();
    const qtd  = parseInt(quantidade.value);
    const desc = descricao.value.trim();

    if (!tipo || !nome || !qtd || qtd < 1 || !desc) {
        alert('Por favor, preencha todos os campos.');
        return;
    }

    alimentos.push({ tipo, nome, qtd, desc });
    renderLista();
    fecharModal();
}

function removerAlimento(index) {
    alimentos.splice(index, 1);
    renderLista();
}

function renderLista() {
    const lista    = document.getElementById('lista');
    const msgVazia = document.getElementById('msgVazia');

    lista.innerHTML = '';

    if (alimentos.length === 0) {
        msgVazia.style.display = 'block';
        return;
    }

    msgVazia.style.display = 'none';

    alimentos.forEach((a, i) => {
        const card = document.createElement('div');
        card.className = 'card-alimento';
        card.innerHTML = `
            <div class="card-info">
                <h3>${a.nome}</h3>
                <p>${a.desc}</p>
            </div>
            <div class="card-tags">
                <span class="tag">${a.tipo}</span>
                <span class="tag">${a.qtd} un.</span>
                <button class="btn-remover" onclick="removerAlimento(${i})" title="Remover">✕</button>
            </div>
        `;
        lista.appendChild(card);
    });
}

// Fechar overlay clicando fora do modal
document.getElementById('overlay').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
