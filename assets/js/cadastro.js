function alternarSenha(id, btn) {
    const campo = document.getElementById(id);
    if (campo.type === 'password') {
        campo.type = 'text';
        btn.textContent = 'visibility';
    } else {
        campo.type = 'password';
        btn.textContent = 'visibility_off';
    }
}

document.querySelectorAll('.olho').forEach(function (btn) {
    btn.textContent = 'visibility_off';
});

document.querySelector('form').addEventListener('submit', function (e) {
    const s1 = document.getElementById('senha').value;
    const s2 = document.getElementById('confirmarSenha').value;
    if (s1 !== s2) {
        e.preventDefault();
        alert('As senhas não coincidem. Por favor, verifique.');
    }
});


function validarEEnviar() {
    const cb = document.getElementById('aceitarTermos');
    const aviso = document.getElementById('avisoTermos');
    const bloco = document.getElementById('blocoTermos');

    // 1. Valida senhas ANTES de qualquer outra coisa
    const s1 = document.getElementById('senha').value;
    const s2 = document.getElementById('confirmarSenha').value;
    if (s1 !== s2) {
        alert('As senhas não coincidem. Por favor, verifique.');
        return; // para aqui, não envia
    }

    // 2. Valida termos
    if (!cb.checked) {
        aviso.classList.add('visivel');
        bloco.classList.add('erro');
        bloco.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    aviso.classList.remove('visivel');
    bloco.classList.remove('erro');
    document.querySelector('form').submit();
}

// O listener de submit pode ser removido ou mantido como fallback

document.getElementById('aceitarTermos').addEventListener('change', function () {
    if (this.checked) {
        document.getElementById('avisoTermos').classList.remove('visivel');
        document.getElementById('blocoTermos').classList.remove('erro');
    }
});