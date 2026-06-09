function abrirModalFoto() {
    document.getElementById("modalFoto").style.display = "block";
}
function fecharModalFoto() {
    document.getElementById("modalFoto").style.display = "none";
}
function salvarFotoUsuario() {
    const url = document.getElementById("urlFotoPerfil").value;
    if (url) {
        document.getElementById("fotoUsuario").src     = url;
        document.getElementById("fotoUsuario").style.display  = "block";
        document.getElementById("fotoPadrao").style.display   = "none";
    }
    fecharModalFoto();
}
function removerFotoUsuario() {
    document.getElementById("fotoUsuario").src     = "";
    document.getElementById("fotoUsuario").style.display  = "none";
    document.getElementById("fotoPadrao").style.display   = "flex";
}

// ── Metas ─────────────────────────────────────────────────
let metas = [];

window.onload = function () {
    carregarMetas();
};

function carregarMetas() {
    fetch('get_metas.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            metas = data.metas;
            renderizarMetas();
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            console.error('Erro ao carregar metas:', data.error);
        }
    })
    .catch(error => console.error('Erro:', error));
}

function abrirModal() {
    document.getElementById("modal-title").innerText = "Nova Meta";
    document.getElementById("nome").value       = "";
    document.getElementById("descricao").value  = "";
    document.getElementById("valorTotal").value = "";
    document.getElementById("valorAtual").value = "";
    document.getElementById("iconeURL").value   = "";
    document.getElementById("modal").style.display = "block";
}

function fecharModal() {
    document.getElementById("modal").style.display = "none";
}

function salvarMeta() {
    const nome       = document.getElementById("nome").value;
    const descricao  = document.getElementById("descricao").value;
    const valorTotal = parseFloat(document.getElementById("valorTotal").value);
    const valorAtual = parseFloat(document.getElementById("valorAtual").value);
    const iconeURL   = document.getElementById("iconeURL").value;

    if (!nome || !valorTotal) {
        showToast('warn', 'Nome e valor total são obrigatórios!');        
        return;
    }

    const data = { nome, descricao, valorTotal, valorAtual, iconeURL };

    fetch('salvar_meta.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fecharModal();
            carregarMetas();
        } else {
            if (data.redirect) {
                showToast('error', 'Sessão expirada. Faça login novamente.', data.error);
                window.location.href = data.redirect;
                return;
            }
            showToast('Erro ao salvar meta: ', data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao salvar meta: ', data.error);
    });
}

async function removerMeta(id) {
    const confirmado = await showConfirm('Atenção!', 'Tem certeza que deseja remover esta meta?');
    if (!confirmado) return;

    fetch('deletar_meta.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            carregarMetas();
        } else {
            if (data.redirect) {
                showToast('error', 'Sessão expirada. Faça login novamente.', data.error);
                window.location.href = data.redirect;
                return;
            }
            showToast('error', 'Erro ao remover meta: ', data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('error', 'Erro ao remover meta: ', data.error);
    });
}

/*Confirm sem pop up personalizado
function removerMeta(id) {
    if (confirm('Tem certeza que deseja remover esta meta?')) {
        fetch('deletar_meta.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                carregarMetas();
            } else {
                if (data.redirect) {
                    showToast('error', 'Sessão expirada. Faça login novamente.', data.error);
                    window.location.href = data.redirect;
                    return;
                }
                showToast('error', 'Erro ao remover meta: ', data.error);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showToast('error', 'Erro ao remover meta: ', data.error);
        });
    }
}
*/

function renderizarMetas() {
    const container = document.getElementById("metas");
    container.innerHTML = "";

    if (metas.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--txt-muted);">
                <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                <p style="font-size: 16px;">Nenhuma meta criada ainda. Clique no botão + para começar!</p>
            </div>
        `;
        return;
    }

    metas.forEach((meta) => {
        const card = document.createElement("div");
        card.className = "meta-card";

        let imagemHTML = meta.iconeURL
            ? `<img src="${meta.iconeURL}" alt="Ícone da meta" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
            : `<div class="meta-img">+</div>`;

        const progresso = meta.progresso || 0;
        const valorFormatado = parseFloat(meta.valorAtual).toFixed(2);
        const totalFormatado = parseFloat(meta.valorTotal).toFixed(2);

        card.innerHTML = `
          ${imagemHTML}
          ${meta.iconeURL ? `<div class="meta-img" style="display: none;">+</div>` : ''}
          <h3>${meta.nome}</h3>
          <p>${meta.descricao || 'Sem descrição'}</p>
          <div class="meta-valor">R$ ${valorFormatado} / R$ ${totalFormatado}</div>
          <div class="progress-bar">
            <div class="progress" style="width:${progresso}%"></div>
          </div>
          <div class="progress-text">${Math.round(progresso)}% concluído</div>
          <div class="action-buttons">
            <button class="remove-btn" onclick="removerMeta(${meta.id})"><i class="fa-solid fa-trash"></i> Remover</button>
          </div>
        `;

        container.appendChild(card);
    });
}

// ── Menu lateral ──────────────────────────────────────────
const itemMenu = document.querySelectorAll('.item-menu');

function selectLink() {
    itemMenu.forEach(item => item.classList.remove('ativo'));
    this.classList.add('ativo');
}
itemMenu.forEach(item => item.addEventListener('click', selectLink));

const btnExpandir = document.querySelector('#bt-exp');
const menuLat     = document.querySelector('.menu-lateral');

btnExpandir.addEventListener('click', function () {
    menuLat.classList.toggle('expandir');
});

// ── Logout ────────────────────────────────────────────────
const btnLogout = document.getElementById('btn-logout');

btnLogout.addEventListener('click', function (e) {
    e.preventDefault();

    fetch('logout.php', {
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
