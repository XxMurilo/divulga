btnLogin = document.getElementById('btnLogin');
btnCadastro = document.getElementById('btnCadastro');

btnLogin.addEventListener('click', redirecionarLogin);
btnCadastro.addEventListener('click', redirecionarCadastro);

function redirecionarLogin() {
    window.location.href = 'login.html';
}   

function redirecionarCadastro() {
    window.location.href = 'cadastro.html';
}