// ── Olho da senha ──────────────────────────────────────────────
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

// ── Mensagem de erro via query param ───────────────────────────
(function () {
    const erro = new URLSearchParams(window.location.search).get('erro');
    if (!erro) return;

    const el = document.getElementById('msgErro');
    if (!el) return;

    const msgs = {
        credenciais: 'E-mail ou senha incorretos. Verifique e tente novamente.',
        campos: 'Preencha o e-mail e a senha corretamente.'
    };

    el.textContent = msgs[erro] || 'Erro ao fazer login. Tente novamente.';
    el.style.display = 'flex';

    // Remove o ?erro= da URL sem recarregar a página
    const url = new URL(window.location.href);
    url.searchParams.delete('erro');
    history.replaceState(null, '', url.pathname);
})();
