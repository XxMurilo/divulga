// ── Elementos do DOM ──────────────────────────────────────────────────────────
const corpoTabela = document.getElementById('corpoTabela');
const mensagem = document.getElementById('mensagem');
const btnNovo = document.getElementById('btnNovo');
const btnAtualizar = document.getElementById('btnAtualizar');
const modal = document.getElementById('overlay');
const tituloModal = document.getElementById('tituloModal');
const formAlimento = document.getElementById('formAlimento');
const fecharModal = document.getElementById('fecharModal');
const btnCancelar = document.getElementById('btnCancelar');

const idAlimentoInput = document.getElementById('idAlimentoDoador');
const tiposAlimento = document.getElementById('tipo');
const nomeInput = document.getElementById('nome');
const quantidadeInput = document.getElementById('quantidade');
const validadeInput = document.getElementById('validade');
const descricaoInput = document.getElementById('descricao');

// ── Eventos ───────────────────────────────────────────────────────────────────
btnAtualizar.addEventListener('click', listarAlimentos);
btnNovo.addEventListener('click', abrirModalNovo);
formAlimento.addEventListener('submit', salvarAlimento);
fecharModal.addEventListener('click', fecharJanelaModal);
btnCancelar.addEventListener('click', fecharJanelaModal);

modal.addEventListener('click', function (event) {
    if (event.target === modal) {
        fecharJanelaModal();
    }
});

// ── LISTAR ────────────────────────────────────────────────────────────────────
function listarAlimentos() {
    console.log('Listar Alimentos');
    corpoTabela.innerHTML = '';
    mensagem.innerText = 'Carregando...';

    fetch('PHP/doadores/listarAlimentos.php')
        .then(function (resposta) {
            if (resposta.status === 401) {
                window.location.href = '../login.html';
                return;
            }
            return resposta.json();
        })
        .then(function (dados) {
            mensagem.innerText = '';

            if (!dados) return;

            if (dados.erro) {
                mensagem.innerText = dados.mensagem;
                return;
            }

            if (dados.alimentos.length === 0) {
                mensagem.innerText = 'Nenhum alimento cadastrado ainda.';
                return;
            }

            dados.alimentos.forEach(function (alimento) {
                criarLinhaTabela(alimento);
            });
        })
        .catch(function (erro) {
            console.error('Erro ao listar alimentos:', erro);
            mensagem.innerText = 'Erro ao carregar alimentos.';
        });
}

// ── CRIAR LINHA DA TABELA ─────────────────────────────────────────────────────
function criarLinhaTabela(alimento) {
    const linha = document.createElement('tr');

    const colunaId = document.createElement('td');
    const colunaNome = document.createElement('td');
    const colunaTipo = document.createElement('td');
    const colunaQtd = document.createElement('td');
    const colunaValidade = document.createElement('td');
    const colunaDesc = document.createElement('td');
    const colunaAcoes = document.createElement('td');

    colunaId.innerText = alimento.id;
    colunaNome.innerText = alimento.nome;
    colunaTipo.innerText = alimento.tipo;
    colunaQtd.innerText = alimento.quantidade;
    colunaValidade.innerText = alimento.validade;
    colunaDesc.innerText = alimento.descricao;

    const btnAlterar = document.createElement('button');
    btnAlterar.innerText = 'ALTERAR';
    btnAlterar.className = 'btn-alterar';
    btnAlterar.addEventListener('click', function () {
        abrirModalAlterar(alimento);
    });

    const btnExcluir = document.createElement('button');
    btnExcluir.innerText = 'EXCLUIR';
    btnExcluir.className = 'btn-remover';
    btnExcluir.addEventListener('click', function () {
        excluirAlimento(alimento);
    });

    colunaAcoes.appendChild(btnAlterar);
    colunaAcoes.appendChild(btnExcluir);

    linha.appendChild(colunaId);
    linha.appendChild(colunaNome);
    linha.appendChild(colunaTipo);
    linha.appendChild(colunaQtd);
    linha.appendChild(colunaValidade);
    linha.appendChild(colunaDesc);
    linha.appendChild(colunaAcoes);

    corpoTabela.appendChild(linha);
}

// ── MODAL NOVO ────────────────────────────────────────────────────────────────
function abrirModalNovo() {
    tituloModal.innerText = 'Novo Alimento para Doação';
    idAlimentoInput.value = '';
    nomeInput.disabled = false;
    tiposAlimento.disabled = false;
    formAlimento.reset();
    modal.style.display = 'flex';
    nomeInput.focus();
}

// ── MODAL ALTERAR ─────────────────────────────────────────────────────────────
function abrirModalAlterar(alimento) {
    tituloModal.innerText = 'Alterar Alimento';
    idAlimentoInput.value = alimento.id;
    nomeInput.value = alimento.nome;
    tiposAlimento.value = alimento.tipo_id;
    quantidadeInput.value = alimento.quantidade;
    validadeInput.value = alimento.validade;
    descricaoInput.value = alimento.descricao;

    // Nome e tipo não podem ser alterados, apenas quantidade, validade e descrição
    nomeInput.disabled = true;
    tiposAlimento.disabled = true;

    modal.style.display = 'flex';
}

// ── FECHAR MODAL ──────────────────────────────────────────────────────────────
function fecharJanelaModal() {
    modal.style.display = 'none';
    formAlimento.reset();
    idAlimentoInput.value = '';
    nomeInput.disabled = false;
    tiposAlimento.disabled = false;
    tituloModal.innerText = 'Novo Alimento para Doação';
}

// ── SALVAR (INSERT / UPDATE) ──────────────────────────────────────────────────
function salvarAlimento(evento) {
    evento.preventDefault();

    const idAlimentoDoador = idAlimentoInput.value;

    if (idAlimentoDoador === '') {

        mensagem.innerText = 'Inserindo alimento...';

        fetch('PHP/doadores/inserirAlimentos.php', {

            method: 'POST',

            body: new URLSearchParams({

                nome: nomeInput.value.trim(),

                tipo: tiposAlimento.value,

                quantidade: quantidadeInput.value,

                validade: validadeInput.value,

                descricao: descricaoInput.value
            })
        })
            .then(function (resposta) {

                return resposta.json();
            })
            .then(function (dados) {

                if (dados.erro) {

                    mensagem.innerText = dados.mensagem;

                    return;
                }

                mensagem.innerText = dados.mensagem;

                fecharJanelaModal();

                listarAlimentos();
            })
            .catch(function (erro) {

                console.log(erro);

                mensagem.innerText =
                    'Erro ao processar a resposta do Servidor.';
            });

    } else {
        // ── ALTERAR ──
        mensagem.innerText = 'Alterando alimento...';

        fetch('PHP/doadores/alterarAlimentos.php?idalimento=' + idAlimentoDoador, {
            method: 'POST',
            body: new URLSearchParams({
                quantidade: quantidadeInput.value,
                validade: validadeInput.value,
                descricao: descricaoInput.value
            })
        })
            .then(function (resposta) {
                return resposta.json();
            })
            .then(function (dados) {
                if (dados.erro) {
                    mensagem.innerText = dados.mensagem;
                    return;
                }
                mensagem.innerText = dados.mensagem;
                fecharJanelaModal();
                listarAlimentos();
            })
            .catch(function (erro) {
                console.log(erro);
                mensagem.innerText = 'Erro ao processar a resposta do Servidor.';
            });
    }
}

// ── EXCLUIR ───────────────────────────────────────────────────────────────────
function excluirAlimento(alimento) {
    const confirmar = confirm('Deseja realmente excluir o alimento "' + alimento.nome + '"?');

    if (!confirmar) return;

    mensagem.innerText = 'Excluindo alimento...';

    fetch('PHP/doadores/excluirAlimentos.php?idalimento=' + alimento.id)
        .then(function (resposta) {
            return resposta.json();
        })
        .then(function (dados) {
            if (dados.erro) {
                mensagem.innerText = dados.mensagem;
                return;
            }
            mensagem.innerText = dados.mensagem;
            listarAlimentos();
        })
        .catch(function (erro) {
            console.log(erro);
            mensagem.innerText = 'Erro ao processar a resposta do Servidor.';
        });
}

function carregarTipos() {

    fetch('PHP/doadores/pesquisarAlimentos.php?tipos=1')
        .then(function (resposta) {
            return resposta.json();
        })
        .then(function (dados) {

            if (dados.erro) {
                mensagem.innerText = dados.mensagem;
                return;
            }

            tiposAlimento.innerHTML =
                '<option value="">Selecione o tipo...</option>';

            dados.tipos.forEach(function (tipo) {

                const option = document.createElement('option');

                option.value = tipo.id;
                option.textContent = tipo.nome;

                tiposAlimento.appendChild(option);
            });
        })
        .catch(function (erro) {
            console.log(erro);
            mensagem.innerText = 'Erro ao carregar tipos.';
        });
}

// ── INICIAR ───────────────────────────────────────────────────────────────────
listarAlimentos();
carregarTipos();