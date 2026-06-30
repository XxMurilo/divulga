// ══════════════════════════════════════════════════════════════════
//  Seeds of Good — Doador SPA
// ══════════════════════════════════════════════════════════════════

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

        if (alvo === 'reservas') carregarReservas();
        if (alvo === 'vencidos') carregarVencidos();
        if (alvo === 'conta')    carregarConta();
    });
});

// ══════════════════════════════════════════════════════════════════
//  SEÇÃO: DOAÇÕES (Meus Alimentos)
// ══════════════════════════════════════════════════════════════════
const mensagem     = document.getElementById('mensagem');
const lista        = document.getElementById('lista');
const msgVazia     = document.getElementById('msgVazia');
const overlay      = document.getElementById('overlay');
const tituloModal  = document.getElementById('tituloModal');
const formAlimento = document.getElementById('formAlimento');
const idAlimentoInput = document.getElementById('idAlimentoDoador');
const tiposAlimento   = document.getElementById('tipo');
const nomeInput       = document.getElementById('nome');
const quantidadeInput = document.getElementById('quantidade');
const validadeInput   = document.getElementById('validade');
const descricaoInput  = document.getElementById('descricao');
const labelNome       = document.getElementById('labelNome');
const fotoAlimento    = document.getElementById('fotoAlimento');
const previewFoto     = document.getElementById('previewFoto');
const imgPreview      = document.getElementById('imgPreview');

// Tipos que são bebidas — label muda para "Nome da Bebida"
const TIPOS_BEBIDA = ['bebidas', 'bebida'];

tiposAlimento.addEventListener('change', function() {
    const tipoNome = (this.options[this.selectedIndex]?.text || '').toLowerCase().trim();
    if (TIPOS_BEBIDA.includes(tipoNome)) {
        labelNome.textContent = 'Nome da Bebida';
        nomeInput.placeholder = 'Ex: Suco de laranja, Leite, Água...';
    } else if (tipoNome && tipoNome !== 'selecione o tipo...') {
        labelNome.textContent = 'Nome do ' + this.options[this.selectedIndex].text;
        nomeInput.placeholder = 'Ex: Banana, Arroz, Leite...';
    } else {
        labelNome.textContent = 'Nome do Alimento';
        nomeInput.placeholder = 'Ex: Banana, Arroz, Leite...';
    }
});

fotoAlimento.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imgPreview.src = e.target.result;
            previewFoto.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    } else {
        previewFoto.style.display = 'none';
        imgPreview.src = '';
    }
});

document.getElementById('btnNovo').addEventListener('click', abrirModalNovo);
document.getElementById('btnCancelar').addEventListener('click', fecharJanelaModal);
formAlimento.addEventListener('submit', salvarAlimento);
overlay.addEventListener('click', function(e) {
    if (e.target === overlay) fecharJanelaModal();
});

function listarAlimentos() {
    lista.innerHTML = '';
    msgVazia.style.display = 'none';
    mensagem.textContent = 'Carregando...';

    fetch('../backend/doadores/listarAlimentos.php')
        .then(function(r) {
            if (r.status === 401) { window.location.href = '../login.html'; return; }
            return r.json();
        })
        .then(function(dados) {
            mensagem.textContent = '';
            if (!dados) return;
            if (dados.erro) { mensagem.textContent = dados.mensagem; return; }
            if (dados.alimentos.length === 0) {
                msgVazia.style.display = 'block';
                return;
            }
            dados.alimentos.forEach(criarCardAlimento);
        })
        .catch(function() { mensagem.textContent = 'Erro ao carregar alimentos.'; });
}

function criarCardAlimento(alimento) {
    const card = document.createElement('div');
    card.className = 'card-alimento';

    const validade = alimento.validade
        ? new Date(alimento.validade + 'T00:00:00').toLocaleDateString('pt-BR')
        : '—';

    const imgHtml = alimento.imagem
        ? '<img class="card-imagem" src="' + esc(alimento.imagem) + '" alt="' + esc(alimento.nome) + '">'
        : '<div class="card-imagem card-imagem-placeholder">🌱</div>';

    card.innerHTML =
        imgHtml +
        '<div class="card-info">' +
            '<h3>' + esc(alimento.nome) + '</h3>' +
            '<p>Validade: ' + validade + ' · ' + esc(alimento.descricao || '') + '</p>' +
        '</div>' +
        '<div class="card-tags">' +
            '<span class="tag">' + esc(alimento.tipo) + '</span>' +
            '<span class="tag tag-qtd">' + alimento.quantidade + ' un.</span>' +
        '</div>' +
        '<div class="card-botoes">' +
            '<button class="btn-alterar">Alterar</button>' +
            '<button class="btn-remover">Excluir</button>' +
        '</div>';

    card.querySelector('.btn-alterar').addEventListener('click', function() {
        abrirModalAlterar(alimento);
    });
    card.querySelector('.btn-remover').addEventListener('click', function() {
        excluirAlimento(alimento);
    });

    lista.appendChild(card);
}

function abrirModalNovo() {
    tituloModal.textContent = 'Novo Alimento para Doação';
    idAlimentoInput.value = '';
    nomeInput.disabled = false;
    tiposAlimento.disabled = false;
    formAlimento.reset();
    labelNome.textContent = 'Nome do Alimento';
    nomeInput.placeholder = 'Ex: Banana, Arroz, Leite...';
    previewFoto.style.display = 'none';
    imgPreview.src = '';
    overlay.style.display = 'flex';
    nomeInput.focus();
}

function abrirModalAlterar(alimento) {
    tituloModal.textContent = 'Alterar Alimento';
    idAlimentoInput.value   = alimento.id;
    nomeInput.value         = alimento.nome;
    tiposAlimento.value     = alimento.tipo_id;
    quantidadeInput.value   = alimento.quantidade;
    validadeInput.value     = alimento.validade;
    descricaoInput.value    = alimento.descricao;
    nomeInput.disabled      = true;
    tiposAlimento.disabled  = true;
    overlay.style.display   = 'flex';
}

function fecharJanelaModal() {
    overlay.style.display = 'none';
    formAlimento.reset();
    idAlimentoInput.value  = '';
    nomeInput.disabled     = false;
    tiposAlimento.disabled = false;
    labelNome.textContent  = 'Nome do Alimento';
    nomeInput.placeholder  = 'Ex: Banana, Arroz, Leite...';
    previewFoto.style.display = 'none';
    imgPreview.src = '';
    tituloModal.textContent = 'Novo Alimento para Doação';
}

function salvarAlimento(e) {
    e.preventDefault();
    const id = idAlimentoInput.value;

    if (id === '') {
        mensagem.textContent = 'Inserindo alimento...';

        const formData = new FormData();
        formData.append('nome',       nomeInput.value.trim());
        formData.append('tipo',       tiposAlimento.value);
        formData.append('quantidade', quantidadeInput.value);
        formData.append('validade',   validadeInput.value);
        formData.append('descricao',  descricaoInput.value);
        if (fotoAlimento.files && fotoAlimento.files[0]) {
            formData.append('foto', fotoAlimento.files[0]);
        }

        fetch('../backend/doadores/inserirAlimentos.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(dados) {
            mensagem.textContent = dados.mensagem;
            if (!dados.erro) { fecharJanelaModal(); listarAlimentos(); }
        })
        .catch(function() { mensagem.textContent = 'Erro ao processar resposta.'; });
    } else {
        mensagem.textContent = 'Alterando alimento...';
        fetch('../backend/doadores/alterarAlimentos.php?idalimento=' + id, {
            method: 'POST',
            body: new URLSearchParams({
                quantidade: quantidadeInput.value,
                validade:   validadeInput.value,
                descricao:  descricaoInput.value
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(dados) {
            mensagem.textContent = dados.mensagem;
            if (!dados.erro) { fecharJanelaModal(); listarAlimentos(); }
        })
        .catch(function() { mensagem.textContent = 'Erro ao processar resposta.'; });
    }
}

function excluirAlimento(alimento) {
    if (!confirm('Deseja realmente excluir o alimento "' + alimento.nome + '"?')) return;
    mensagem.textContent = 'Excluindo...';
    fetch('../backend/doadores/excluirAlimentos.php?idalimento=' + alimento.id)
        .then(function(r) { return r.json(); })
        .then(function(dados) {
            mensagem.textContent = dados.mensagem;
            if (!dados.erro) listarAlimentos();
        })
        .catch(function() { mensagem.textContent = 'Erro ao excluir.'; });
}

function carregarTipos() {
    fetch('../backend/doadores/pesquisarAlimentos.php?tipos=1')
        .then(function(r) { return r.json(); })
        .then(function(dados) {
            if (dados.erro) return;
            tiposAlimento.innerHTML = '<option value="">Selecione o tipo...</option>';
            dados.tipos.forEach(function(tipo) {
                const opt = document.createElement('option');
                opt.value       = tipo.id;
                opt.textContent = tipo.nome;
                tiposAlimento.appendChild(opt);
            });
        });
}

// ══════════════════════════════════════════════════════════════════
//  SEÇÃO: RESERVAS RECEBIDAS
// ══════════════════════════════════════════════════════════════════
const mensagemReservas   = document.getElementById('mensagemReservas');
const listaReservasCards = document.getElementById('listaReservasCards');
const msgVaziaReservas   = document.getElementById('msgVaziaReservas');

function carregarReservas() {
    listaReservasCards.innerHTML = '';
    msgVaziaReservas.style.display = 'none';
    mensagemReservas.textContent = 'Carregando reservas...';

    fetch('../backend/doadores/listarReservas.php')
        .then(function(r) {
            if (r.status === 401) { window.location.href = '../login.html'; return; }
            return r.json();
        })
        .then(function(dados) {
            mensagemReservas.textContent = '';
            if (!dados) return;
            if (dados.erro) { mensagemReservas.textContent = dados.mensagem; return; }
            if (dados.reservas.length === 0) {
                msgVaziaReservas.style.display = 'block';
                return;
            }
            dados.reservas.forEach(criarCardReserva);
        })
        .catch(function() { mensagemReservas.textContent = 'Erro ao carregar reservas.'; });
}

function criarCardReserva(reserva) {
    const card = document.createElement('div');
    card.className = 'card-alimento card-reserva';

    const statusClasse = {
        'Disponível': 'status-disponivel',
        'Reservado':  'status-reservado',
        'Cancelado':  'status-cancelado'
    }[reserva.status] || '';

    const imgHtml = reserva.imagem
        ? '<img class="card-imagem" src="' + esc(reserva.imagem) + '" alt="' + esc(reserva.alimento_nome) + '">'
        : '<div class="card-imagem card-imagem-placeholder">🌱</div>';

    card.innerHTML =
        imgHtml +
        '<div class="card-info">' +
            '<h3>' + esc(reserva.alimento_nome) + '</h3>' +
            '<p>Reservado por: <strong>' + esc(reserva.recebedor_nome) + '</strong></p>' +
            '<p>Contato: ' + esc(reserva.recebedor_email || '') +
                (reserva.recebedor_telefone ? ' · ' + esc(reserva.recebedor_telefone) : '') +
            '</p>' +
        '</div>' +
        '<div class="card-tags">' +
            '<span class="tag tag-qtd">' + reserva.quantidade_reservada + ' un.</span>' +
            '<span class="tag ' + statusClasse + '">' + esc(reserva.status) + '</span>' +
        '</div>' +
        '<div class="card-botoes reserva-botoes">' +
            (reserva.status === 'Reservado'
                ? '<button class="btn-confirmar-reserva" data-id="' + reserva.idreserva + '">✔ Confirmar</button>' +
                  '<button class="btn-cancelar-reserva"  data-id="' + reserva.idreserva + '">✖ Cancelar</button>'
                : '') +
            '<button class="btn-denunciar-reserva" data-id="' + reserva.idreserva + '">⚑ Denunciar</button>' +
        '</div>';

    const btnConfirmar = card.querySelector('.btn-confirmar-reserva');
    const btnCancelar  = card.querySelector('.btn-cancelar-reserva');
    const btnDenunciar = card.querySelector('.btn-denunciar-reserva');

    if (btnConfirmar) btnConfirmar.addEventListener('click', function() {
        atualizarStatusReserva(reserva.idreserva, 4, card); // 4 = Entregue
    });
    if (btnCancelar) btnCancelar.addEventListener('click', function() {
        atualizarStatusReserva(reserva.idreserva, 3, card); // 3 = Cancelado
    });
    if (btnDenunciar) btnDenunciar.addEventListener('click', function() {
        abrirModalDenuncia(reserva);
    });

    listaReservasCards.appendChild(card);
}

function atualizarStatusReserva(idreserva, idstatus, card) {
    mensagemReservas.textContent = 'Atualizando...';
    fetch('../backend/doadores/atualizarReserva.php', {
        method: 'POST',
        body: new URLSearchParams({ idreserva: idreserva, idstatus: idstatus })
    })
    .then(function(r) { return r.json(); })
    .then(function(dados) {
        mensagemReservas.textContent = dados.mensagem;
        if (!dados.erro) {
            if (card && card.parentNode) card.parentNode.removeChild(card);
            if (listaReservasCards.children.length === 0) {
                msgVaziaReservas.style.display = 'block';
            }
        }
    })
    .catch(function() { mensagemReservas.textContent = 'Erro ao atualizar reserva.'; });
}

// ══════════════════════════════════════════════════════════════════
//  SEÇÃO: ALIMENTOS VENCIDOS
// ══════════════════════════════════════════════════════════════════
const mensagemVencidos   = document.getElementById('mensagemVencidos');
const listaVencidosCards = document.getElementById('listaVencidosCards');
const msgVaziaVencidos   = document.getElementById('msgVaziaVencidos');

function carregarVencidos() {
    listaVencidosCards.innerHTML = '';
    msgVaziaVencidos.style.display = 'none';
    mensagemVencidos.textContent = 'Carregando...';

    fetch('../backend/doadores/listarAlimentosVencidos.php')
        .then(function(r) {
            if (r.status === 401) { window.location.href = '../login.html'; return; }
            return r.json();
        })
        .then(function(dados) {
            mensagemVencidos.textContent = '';
            if (!dados) return;
            if (dados.erro) { mensagemVencidos.textContent = dados.mensagem; return; }
            if (dados.alimentos.length === 0) {
                msgVaziaVencidos.style.display = 'block';
                return;
            }
            dados.alimentos.forEach(criarCardVencido);
        })
        .catch(function() { mensagemVencidos.textContent = 'Erro ao carregar alimentos vencidos.'; });
}

function criarCardVencido(alimento) {
    const card = document.createElement('div');
    card.className = 'card-alimento card-vencido';

    const validade = alimento.validade
        ? new Date(alimento.validade + 'T00:00:00').toLocaleDateString('pt-BR')
        : '—';

    const imgHtml = alimento.imagem
        ? '<img class="card-imagem" src="' + esc(alimento.imagem) + '" alt="' + esc(alimento.nome) + '">'
        : '<div class="card-imagem card-imagem-placeholder">🚫</div>';

    card.innerHTML =
        imgHtml +
        '<div class="card-info">' +
            '<h3>' + esc(alimento.nome) + '</h3>' +
            '<p class="card-descricao">' + esc(alimento.descricao || '—') + '</p>' +
            '<p class="card-validade">📅 Vencido em: <strong>' + validade + '</strong></p>' +
        '</div>' +
        '<div class="card-tags">' +
            '<span class="tag">' + esc(alimento.tipo) + '</span>' +
            '<span class="tag tag-qtd">' + alimento.quantidade + ' un.</span>' +
            '<span class="tag tag-vencido">Vencido</span>' +
        '</div>';

    listaVencidosCards.appendChild(card);
}

// ══════════════════════════════════════════════════════════════════
//  SEÇÃO: DENÚNCIA (modal global)
// ══════════════════════════════════════════════════════════════════
const overlayDenuncia    = document.getElementById('overlayDenuncia');
const denunciaIdUsuario  = document.getElementById('denunciaIdUsuario');
const denunciaReservaInfo = document.getElementById('denunciaReservaInfo');
const denunciaIdReserva  = document.getElementById('denunciaIdReserva');
const denunciaMotivo     = document.getElementById('denunciaMotivo');
const denunciaContador   = document.getElementById('denunciaContador');
const mensagemDenuncia   = document.getElementById('mensagemDenuncia');

denunciaMotivo.addEventListener('input', function() {
    denunciaContador.textContent = this.value.length;
});

document.getElementById('btnCancelarDenuncia').addEventListener('click', fecharModalDenuncia);
overlayDenuncia.addEventListener('click', function(e) {
    if (e.target === overlayDenuncia) fecharModalDenuncia();
});

document.getElementById('btnEnviarDenuncia').addEventListener('click', enviarDenuncia);

var _nomeUsuarioSessao = '';
fetch('../backend/doadores/minhaConta.php')
    .then(function(r) { return r.json(); })
    .then(function(dados) {
        if (dados && !dados.erro && dados.usuario) {
            _nomeUsuarioSessao = dados.usuario.nome || '';
        }
    });

function abrirModalDenuncia(reserva) {
    denunciaIdUsuario.value   = _nomeUsuarioSessao ? _nomeUsuarioSessao : 'Carregando...';
    denunciaReservaInfo.value = 'Reserva #' + reserva.idreserva + ' — ' + esc(reserva.alimento_nome) +
                                ' (' + reserva.quantidade_reservada + ' un.) — ' + esc(reserva.status);
    denunciaIdReserva.value   = reserva.idreserva;
    denunciaMotivo.value      = '';
    denunciaContador.textContent = '0';
    mensagemDenuncia.textContent = '';
    mensagemDenuncia.style.color = '';
    overlayDenuncia.style.display = 'flex';
    denunciaMotivo.focus();
}

function fecharModalDenuncia() {
    overlayDenuncia.style.display = 'none';
    denunciaMotivo.value = '';
    denunciaContador.textContent = '0';
    mensagemDenuncia.textContent = '';
}

function enviarDenuncia() {
    const motivo    = denunciaMotivo.value.trim();
    const idReserva = denunciaIdReserva.value;

    if (motivo.length < 10) {
        mensagemDenuncia.textContent = 'O motivo deve ter pelo menos 10 caracteres.';
        mensagemDenuncia.style.color = '#b45309';
        return;
    }

    mensagemDenuncia.textContent = 'Enviando denúncia...';
    mensagemDenuncia.style.color = '';

    fetch('../backend/doadores/criarDenuncia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idreserva: parseInt(idReserva, 10), motivo: motivo })
    })
    .then(function(r) { return r.json(); })
    .then(function(dados) {
        if (dados.erro) {
            mensagemDenuncia.textContent = dados.mensagem;
            mensagemDenuncia.style.color = '#b45309';
        } else {
            mensagemDenuncia.textContent = '✔ ' + dados.mensagem;
            mensagemDenuncia.style.color = '#065f46';
            setTimeout(fecharModalDenuncia, 1800);
        }
    })
    .catch(function() {
        mensagemDenuncia.textContent = 'Erro ao enviar denúncia.';
        mensagemDenuncia.style.color = '#b45309';
    });
}

// ══════════════════════════════════════════════════════════════════
//  SEÇÃO: MINHA CONTA
// ══════════════════════════════════════════════════════════════════
const dadosConta       = document.getElementById('dadosConta');
const avatarInicial    = document.getElementById('avatarInicial');
const cardVisualizacao = document.getElementById('cardVisualizacao');
const cardEdicao       = document.getElementById('cardEdicao');
const mensagemConta    = document.getElementById('mensagemConta');

document.getElementById('btnEditarConta').addEventListener('click', function() {
    cardVisualizacao.style.display = 'none';
    cardEdicao.style.display = 'block';
    mensagemConta.textContent = '';
});

document.getElementById('btnCancelarEdicao').addEventListener('click', function() {
    cardEdicao.style.display = 'none';
    cardVisualizacao.style.display = 'block';
    mensagemConta.textContent = '';
});

document.getElementById('btnSalvarConta').addEventListener('click', salvarConta);

// ── Logout ────────────────────────────────────────────────────────
const overlayLogout = document.getElementById('overlayLogout');

document.getElementById('btnSair').addEventListener('click', function() {
    overlayLogout.style.display = 'flex';
});
document.getElementById('btnCancelarLogout').addEventListener('click', function() {
    overlayLogout.style.display = 'none';
});
document.getElementById('btnConfirmarLogout').addEventListener('click', function() {
    fetch('../backend/logout.php')
        .then(function()  { window.location.href = '../index.html'; })
        .catch(function() { window.location.href = '../index.html'; });
});
overlayLogout.addEventListener('click', function(e) {
    if (e.target === overlayLogout) overlayLogout.style.display = 'none';
});

function carregarConta() {
    dadosConta.innerHTML = '<p class="carregando-conta">Carregando dados...</p>';
    fetch('../backend/doadores/minhaConta.php')
        .then(function(r) {
            if (r.status === 401) { window.location.href = '../login.html'; return; }
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

    fetch('../backend/doadores/atualizarConta.php', {
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

async function verificarAutenticacaoEIniciar() {
    try {
        // 1. Verifica a autenticação básica
        const respostaAutenticacao = await fetch('../backend/doadores/minhaConta.php');
        
        if (!respostaAutenticacao.ok || respostaAutenticacao.status === 401 || respostaAutenticacao.status === 403) {
            window.location.href = '../login.html';
            return;
        }

        const dados = await respostaAutenticacao.json();

        if (!dados || dados.erro || !dados.usuario) {
            window.location.href = '../login.html';
            return;
        }

        // Define o nome do usuário na sessão
        _nomeUsuarioSessao = dados.usuario.nome || '';
        
        // 2. Verifica a condição do usuário (agora dentro do mesmo escopo)
        const responseCondicao = await fetch('../backend/verifyCondicao.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: dados.usuario.idusuario })
        });
        
        if (!responseCondicao.ok) {
            throw new Error("Erro ao verificar condição no servidor");
        }

        const respostaCondicao = await responseCondicao.json();

        if (respostaCondicao.erro === true) {
            alert(respostaCondicao.mensagem);
            window.location.href = '../index.html';
            return; // Interrompe a execução aqui
        } 
        
        if (respostaCondicao.SystemError === true) {
            console.error(respostaCondicao.mensagem);
            alert('Erro na requisição');
            window.location.href = '../login.html';
            return;
        }

        console.log(respostaCondicao.mensagem);

        // 3. Se passou por TODAS as validações, inicia a tela com segurança
        carregarConta(); 
        
    } catch (erro) {
        console.error("Erro no fluxo de verificação:", erro);
        window.location.href = '../login.html';
    }
}

// ── Utilitário: escapa HTML ────────────────────────────────────────
function esc(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Inicializar ───────────────────────────────────────────────────
listarAlimentos();
carregarTipos();
verificarAutenticacaoEIniciar();