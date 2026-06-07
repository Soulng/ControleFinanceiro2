// ── Menu lateral ──────────────────────────────────────────
const API_BASE = window.location.protocol === 'file:'
  ? 'http://127.0.0.1:8080/ControleFinanceiro'
  : window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');

const itemMenu = document.querySelectorAll('.item-menu');

function selectLink() {
    itemMenu.forEach(item => item.classList.remove('ativo'));
    this.classList.add('ativo');
}
itemMenu.forEach(item => item.addEventListener('click', selectLink));

const btnExpandir  = document.querySelector('#bt-exp');
const menuLat      = document.querySelector('.menu-lateral');
const conteudoMain = document.querySelector('.main-content');

btnExpandir.addEventListener('click', function () {
    menuLat.classList.toggle('expandir');
    conteudoMain.classList.toggle('expandir');
});

// ── Logout ────────────────────────────────────────────────
const btnLogout = document.getElementById('btn-logout');

btnLogout.addEventListener('click', function (e) {
    e.preventDefault();

    fetch(`${API_BASE}/logout.php`, {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(() => {
        localStorage.removeItem('currentUserName');
        window.location.href = 'index.html';
    })
    .catch(() => {
        localStorage.removeItem('currentUserName');
        window.location.href = 'index.html';
    });
});
