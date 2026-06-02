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

document.querySelectorAll('.olho').forEach(function(btn) {
    btn.textContent = 'visibility_off';
});

document.querySelector('form').addEventListener('submit', function(e) {
    const s1 = document.getElementById('senha').value;
    const s2 = document.getElementById('confirmarSenha').value;
    if (s1 !== s2) {
        e.preventDefault();
        alert('As senhas não coincidem. Por favor, verifique.');
    }
});