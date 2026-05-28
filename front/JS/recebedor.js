// ══════════════════════════════════════════════════════════════════
//  Seeds of Good — Recebedor
// ══════════════════════════════════════════════════════════════════

// ── Navegação entre seções ──────────────────────────────────────────
var navLinks = document.querySelectorAll('.nav-link');
var secoes   = document.querySelectorAll('.secao');

navLinks.forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        var alvo = this.dataset.secao;
        navLinks.forEach(function(l) { l.classList.remove('ativo'); });
        secoes.forEach(function(s)   { s.classList.remove('ativa'); });
        this.classList.add('ativo');
        document.getElementById('secao-' + alvo).classList.add('ativa');

        if (alvo === 'reservas') carregarReservas();
        if (alvo === 'alimentos') carregarAlimentos();
    });
});

// ══════════════════════════════════════════════════════════════════
//  SEÇÃO: ALIMENTOS DISPONÍVEIS
// ══════════════════════════════════════════════════════════════════

var mensagemAlimentos   = document.getElementById('mensagemAlimentos');
var listaAlimentosCards = document.getElementById('listaAlimentosCards');
var msgVaziaAlimentos   = document.getElementById('msgVaziaAlimentos');

function carregarAlimentos() {
    listaAlimentosCards.innerHTML = '';
    msgVaziaAlimentos.style.display = 'none';
    mensagemAlimentos.textContent = 'Carregando...';

    fetch('PHP/recebedor/listarAlimentos.php')
        .then(function(r) {
            if (r.status === 401) { window.location.href = 'login.html'; return null; }
            return r.json();
        })
        .then(function(dados) {
            mensagemAlimentos.textContent = '';
            if (!dados) return;
            if (dados.erro) { mensagemAlimentos.textContent = dados.erro; return; }
            if (dados.alimentos.length === 0) {
                msgVaziaAlimentos.style.display = 'block';
                return;
            }
            dados.alimentos.forEach(criarCardAlimento);
        })
        .catch(function() { mensagemAlimentos.textContent = 'Erro ao carregar alimentos.'; });
}

function criarCardAlimento(alimento) {
    var card = document.createElement('div');
    card.className = 'card-alimento';

    var validade = alimento.validade
        ? new Date(alimento.validade + 'T00:00:00').toLocaleDateString('pt-BR')
        : '—';

    card.innerHTML =
        '<div class="card-info">' +
            '<h3>' + esc(alimento.nomeAlimento) + '</h3>' +
            '<p>' + esc(alimento.descricao || '') + '</p>' +
            '<p>Doador: <strong>' + esc(alimento.nomeDoador) + '</strong> · ' + esc(alimento.enderecoDoador || '') + '</p>' +
        '</div>' +
        '<div class="card-tags">' +
            '<span class="tag">Val: ' + validade + '</span>' +
            '<span class="tag">' + alimento.quantidade + ' un.</span>' +
        '</div>' +
        '<button class="btn-reservar">Reservar</button>';

    card.querySelector('.btn-reservar').addEventListener('click', function() {
        abrirModalReserva(alimento);
    });

    listaAlimentosCards.appendChild(card);
}

// ══════════════════════════════════════════════════════════════════
//  MODAL: RESERVAR ALIMENTO
// ══════════════════════════════════════════════════════════════════

var overlayReserva    = document.getElementById('overlayReserva');
var infoAlimento      = document.getElementById('infoAlimento');
var qtdDisponivel     = document.getElementById('qtdDisponivel');
var qtdReserva        = document.getElementById('qtdReserva');
var idAlimentoReserva = document.getElementById('idAlimentoReserva');

document.getElementById('btnCancelarReserva').addEventListener('click', fecharModalReserva);
document.getElementById('btnConfirmarReserva').addEventListener('click', confirmarReserva);

overlayReserva.addEventListener('click', function(e) {
    if (e.target === overlayReserva) fecharModalReserva();
});

function abrirModalReserva(alimento) {
    var validade = alimento.validade
        ? new Date(alimento.validade + 'T00:00:00').toLocaleDateString('pt-BR')
        : '—';

    infoAlimento.innerHTML =
        '<strong>' + esc(alimento.nomeAlimento) + '</strong>' +
        '<span>' + esc(alimento.descricao || 'Sem descrição.') + '</span><br>' +
        '<span>Doador: ' + esc(alimento.nomeDoador) + '</span><br>' +
        '<span>Endereço: ' + esc(alimento.enderecoDoador || '—') + '</span><br>' +
        '<span>Validade: ' + validade + '</span>';

    qtdDisponivel.textContent = alimento.quantidade;
    qtdReserva.value = '';
    qtdReserva.max = alimento.quantidade;
    idAlimentoReserva.value = alimento.idAlimentoDoador;

    overlayReserva.classList.add('ativo');
}

function fecharModalReserva() {
    overlayReserva.classList.remove('ativo');
}

function confirmarReserva() {
    var id  = parseInt(idAlimentoReserva.value);
    var qtd = parseInt(qtdReserva.value);

    if (!qtd || qtd <= 0) {
        alert('Informe uma quantidade válida.');
        return;
    }

    fetch('PHP/recebedor/criarReserva.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idAlimentoDoador: id, quantidade: qtd })
    })
        .then(function(r) { return r.json(); })
        .then(function(dados) {
            if (dados.erro) { alert(dados.erro); return; }
            fecharModalReserva();
            carregarAlimentos();
        })
        .catch(function() { alert('Erro ao fazer reserva.'); });
}

// ══════════════════════════════════════════════════════════════════
//  SEÇÃO: MINHAS RESERVAS
// ══════════════════════════════════════════════════════════════════

var mensagemReservas   = document.getElementById('mensagemReservas');
var listaReservasCards = document.getElementById('listaReservasCards');
var msgVaziaReservas   = document.getElementById('msgVaziaReservas');

function carregarReservas() {
    listaReservasCards.innerHTML = '';
    msgVaziaReservas.style.display = 'none';
    mensagemReservas.textContent = 'Carregando...';

    fetch('PHP/recebedor/listarReservas.php')
        .then(function(r) {
            if (r.status === 401) { window.location.href = 'login.html'; return null; }
            return r.json();
        })
        .then(function(dados) {
            mensagemReservas.textContent = '';
            if (!dados) return;
            if (dados.erro) { mensagemReservas.textContent = dados.erro; return; }
            if (dados.reservas.length === 0) {
                msgVaziaReservas.style.display = 'block';
                return;
            }
            dados.reservas.forEach(criarCardReserva);
        })
        .catch(function() { mensagemReservas.textContent = 'Erro ao carregar reservas.'; });
}

function criarCardReserva(reserva) {
    var card = document.createElement('div');
    card.className = 'card-reserva';

    var validade = reserva.validade
        ? new Date(reserva.validade + 'T00:00:00').toLocaleDateString('pt-BR')
        : '—';

    var statusClasses = {
        'Reservado':  'status-reservado',
        'Cancelado':  'status-cancelado',
        'Entregue':   'status-entregue',
        'Disponível': 'status-disponivel'
    };
    var statusClasse = statusClasses[reserva.statusReserva] || '';

    // Botão cancelar só aparece se status é Reservado (idStatus = 2)
    var btnCancelarHtml = (reserva.idStatus == 2)
        ? '<button class="btn-cancelar-reserva">✖ Cancelar</button>'
        : '';

    card.innerHTML =
        '<div class="card-info">' +
            '<h3>' + esc(reserva.nomeAlimento) + '</h3>' +
            '<p>Doador: <strong>' + esc(reserva.nomeDoador) + '</strong></p>' +
            '<p>Validade: ' + validade + ' · Quantidade: ' + reserva.quantidadeReservada + ' un.</p>' +
        '</div>' +
        '<div class="card-tags">' +
            '<span class="tag ' + statusClasse + '">' + esc(reserva.statusReserva) + '</span>' +
        '</div>' +
        '<div class="card-acoes">' +
            btnCancelarHtml +
            '<button class="btn-denuncia-reserva">⚑ Denunciar Doador</button>' +
        '</div>';

    var btnCancelar = card.querySelector('.btn-cancelar-reserva');
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            if (!confirm('Tem certeza que deseja cancelar esta reserva?')) return;
            cancelarReserva(reserva.idReserva, card);
        });
    }

    card.querySelector('.btn-denuncia-reserva').addEventListener('click', function() {
        abrirModalDenuncia(reserva.idDoador, reserva.nomeDoador);
    });

    listaReservasCards.appendChild(card);
}

function cancelarReserva(idReserva, card) {
    fetch('PHP/recebedor/cancelarReserva.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idReserva: idReserva })
    })
        .then(function(r) { return r.json(); })
        .then(function(dados) {
            if (dados.erro) { alert(dados.erro); return; }
            card.remove();
            if (listaReservasCards.children.length === 0) {
                msgVaziaReservas.style.display = 'block';
            }
        })
        .catch(function() { alert('Erro ao cancelar reserva.'); });
}

// ══════════════════════════════════════════════════════════════════
//  MODAL: DENÚNCIA
// ══════════════════════════════════════════════════════════════════

var overlayDenuncia = document.getElementById('overlayDenuncia');

document.getElementById('btnCancelarDenuncia').addEventListener('click', function() {
    overlayDenuncia.classList.remove('ativo');
});

overlayDenuncia.addEventListener('click', function(e) {
    if (e.target === overlayDenuncia) overlayDenuncia.classList.remove('ativo');
});

function abrirModalDenuncia(idDoador, nomeDoador) {
    document.getElementById('denunciaMotivo').value = '';
    document.getElementById('denunciaIdDoador').value = idDoador || '';

    var infoEl = document.getElementById('denunciaDoadorNome');
    if (nomeDoador) {
        infoEl.textContent = 'Denunciando o doador: ' + nomeDoador;
        infoEl.style.display = 'block';
    } else {
        infoEl.textContent = '';
        infoEl.style.display = 'none';
    }

    overlayDenuncia.classList.add('ativo');
}

document.getElementById('btnEnviarDenuncia').addEventListener('click', function() {
    var motivo   = document.getElementById('denunciaMotivo').value.trim();
    var idDoador = document.getElementById('denunciaIdDoador').value;

    if (motivo.length < 10) {
        alert('O motivo deve ter pelo menos 10 caracteres.');
        return;
    }

    if (!idDoador || parseInt(idDoador) <= 0) {
        alert('Doador não identificado. Abra a denúncia pelo card da reserva.');
        return;
    }

    fetch('PHP/recebedor/criarDenuncia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            motivo:           motivo,
            idUsuarioCulpado: parseInt(idDoador)
        })
    })
        .then(function(r) { return r.json(); })
        .then(function(dados) {
            if (dados.erro) { alert(dados.erro); return; }
            alert('Denúncia enviada com sucesso!');
            overlayDenuncia.classList.remove('ativo');
        })
        .catch(function() { alert('Erro ao enviar denúncia.'); });
});

// ── Utilitário: escapar HTML ────────────────────────────────────────
function esc(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Inicialização ───────────────────────────────────────────────────
carregarAlimentos();