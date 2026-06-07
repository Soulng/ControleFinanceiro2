// ── Menu lateral ──────────────────────────────────────────
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

// ── Dark Mode (padrão dashboard/investimentos) ─────────────
function aplicarTema(tema) {
    document.documentElement.setAttribute('data-tema', tema);
    const escuro = tema === 'escuro';
    document.getElementById('iconeTema').className = escuro ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    document.getElementById('textoTema').textContent = escuro ? 'Modo Claro' : 'Modo Escuro';
    localStorage.setItem('financeTema', tema);
}
function alternarTema() {
    const atual = document.documentElement.getAttribute('data-tema');
    aplicarTema(atual === 'escuro' ? 'claro' : 'escuro');
}
aplicarTema(localStorage.getItem('financeTema') || 'claro');

// ── Popup do formulário ───────────────────────────────────
const btnAbrirFormAdd  = document.querySelector('.btn-add-gastos');
const btnFecharFormAdd = document.querySelector('.btn-fechar-form');

btnAbrirFormAdd.addEventListener('click', function () {
    document.querySelector('.popup-form-add').style.display = 'flex';
});
btnFecharFormAdd.addEventListener('click', function () {
    document.querySelector('.popup-form-add').style.display = 'none';
});

// ── Exibe nome do usuário ──────────────────────────────────
const nomeDisplay = document.getElementById('user-nome-display');
if (nomeDisplay) {
    nomeDisplay.textContent = localStorage.getItem('currentUserName') || '';
}

// ── Estado local de transações ────────────────────────────
let transacoes = [];

// ── Filtros ────────────────────────────────────────────────
const filtroTipo     = document.getElementById('filtro-tipo');
const filtroCateg    = document.getElementById('filtro-categoria');
const filtroDataIni  = document.getElementById('filtro-data-ini');
const filtroDataFim  = document.getElementById('filtro-data-fim');
const filtroBusca    = document.getElementById('filtro-busca');
const btnLimpar      = document.getElementById('btn-limpar-filtros');

[filtroTipo, filtroCateg, filtroDataIni, filtroDataFim].forEach(el =>
    el.addEventListener('change', renderTable)
);
filtroBusca.addEventListener('input', renderTable);

btnLimpar.addEventListener('click', () => {
    filtroTipo.value    = '';
    filtroCateg.value   = '';
    filtroDataIni.value = '';
    filtroDataFim.value = '';
    filtroBusca.value   = '';
    renderTable();
});

function getTransacoesFiltradas() {
    const tipo    = filtroTipo.value.toLowerCase();
    const categ   = filtroCateg.value.toLowerCase();
    const dataIni = filtroDataIni.value;
    const dataFim = filtroDataFim.value;
    const busca   = filtroBusca.value.toLowerCase().trim();

    return transacoes.filter(t => {
        const tipoRaw = (t.tipo || '').toLowerCase();
        const isGasto = (tipoRaw === 'gasto' || tipoRaw === 'despesa' || tipoRaw === 'option1');
        const tipoNorm = isGasto ? 'despesa' : 'receita';

        if (tipo   && tipoNorm !== tipo)                              return false;
        if (categ  && (t.categoria || '').toLowerCase() !== categ)   return false;
        if (dataIni && t.data < dataIni)                             return false;
        if (dataFim && t.data > dataFim)                             return false;
        if (busca  && !(t.descricao || '').toLowerCase().includes(busca)) return false;
        return true;
    });
}

// ── Formulário de adição ──────────────────────────────────
const formAddReg = document.querySelector('.form-add-mov');

formAddReg.addEventListener('submit', (e) => {
    e.preventDefault();

    const dataReg    = document.getElementById('extrato-data').value;
    const descReg    = document.getElementById('descricao-form').value;
    const categReg   = document.getElementById('extrato-categ');
    const tipoReg    = document.getElementById('extrato-tipo');
    const inputValor = document.getElementById('valor-form');

    const valorReg = parseFloat(
        inputValor.value.replace('R$', '').replace(/\s/g, '').replace(',', '.')
    ) || 0;

    if (!dataReg || !descReg || valorReg <= 0) {
        showToast('warn', 'Campos obrigatórios — preencha todos os campos!');
        return;
    }

    const novoRegistro = {
        codigo:    Date.now(),
        data:      dataReg,
        descricao: descReg,
        categoria: categReg.options[categReg.selectedIndex].text,
        tipo:      tipoReg.options[tipoReg.selectedIndex].text,
        valor:     valorReg
    };

    fetch('salvar_transacao.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(novoRegistro)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            transacoes.push(novoRegistro);
            renderTable();
            formAddReg.reset();
            document.querySelector('.popup-form-add').style.display = 'none';
            showToast('success', 'Registro salvo com sucesso!');
        } else {
            if (data.redirect) {
                showToast('warn', 'Sessão expirada. Faça login novamente.');
                window.location.href = data.redirect;
                return;
            }
            showToast('error', data.error || 'Erro ao salvar.');
        }
    })
    .catch(() => showToast('error', 'Falha na conexão com o servidor.'));
});

// ── Renderização da tabela ────────────────────────────────
function renderTable() {
    const tableBody = document.querySelector('.tabela-extrato tbody');
    tableBody.innerHTML = '';

    const lista = getTransacoesFiltradas();

    if (lista.length === 0) {
        const emptyRow  = tableBody.insertRow();
        const emptyCell = emptyRow.insertCell();
        emptyCell.colSpan = 8;
        emptyCell.style.textAlign = 'center';
        emptyCell.style.padding   = '2rem';

        const msg = document.createElement('p');
        msg.textContent = transacoes.length === 0
            ? 'Nenhum registro encontrado. Adicione sua primeira transação!'
            : 'Nenhum registro encontrado para os filtros aplicados.';
        msg.style.cssText = 'color:var(--text-muted);font-size:0.95rem;';
        emptyCell.appendChild(msg);

        // Saldo considera todas as transações, não apenas as filtradas
        atualizarSaldo(calcularSaldo(transacoes));
        return;
    }

    // Saldo corrido calculado sobre a lista filtrada
    let currentBalance = 0;

    lista.forEach(t => {
        const amount  = t.valor;
        const tipoRaw = (t.tipo || '').toLowerCase();
        const isGasto = (tipoRaw === 'gasto' || tipoRaw === 'despesa' || tipoRaw === 'option1');

        currentBalance += isGasto ? -amount : amount;

        const newRow = tableBody.insertRow();

        newRow.insertCell().textContent = t.codigo;
        newRow.insertCell().textContent = t.data;
        newRow.insertCell().textContent = t.descricao;

        const typeCell = newRow.insertCell();
        typeCell.textContent = t.tipo;
        typeCell.style.color = isGasto ? '#ee2626' : '#31c931';

        newRow.insertCell().textContent = t.categoria;

        const valorCell     = newRow.insertCell();
        const valorFormatado = amount.toLocaleString('pt-BR', {
            style: 'currency', currency: 'BRL', minimumFractionDigits: 2
        }).replace('R$', '');
        valorCell.textContent = isGasto ? `- ${valorFormatado}` : `+ ${valorFormatado}`;
        valorCell.style.color = isGasto ? '#df0a0a' : '#0de40d';

        const balanceCell = newRow.insertCell();
        balanceCell.textContent = currentBalance.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        balanceCell.style.color = currentBalance >= 0 ? '#2A9D8F' : '#E76F51';

        const actionsCell = newRow.insertCell();
        const removeBtn   = document.createElement('button');
        removeBtn.textContent     = 'Remover';
        removeBtn.className       = 'btn-action btn-remove';
        removeBtn.dataset.codeRem = t.codigo;
        actionsCell.appendChild(removeBtn);
    });

    // Saldo exibido sempre reflete o total geral (sem filtro)
    atualizarSaldo(calcularSaldo(transacoes));
}

function calcularSaldo(lista) {
    return lista.reduce((acc, t) => {
        const tipoRaw = (t.tipo || '').toLowerCase();
        const isGasto = (tipoRaw === 'gasto' || tipoRaw === 'despesa' || tipoRaw === 'option1');
        return acc + (isGasto ? -t.valor : t.valor);
    }, 0);
}

function atualizarSaldo(valor) {
    const el = document.getElementById('saldo-atual-user');
    if (!el) return;
    el.textContent = valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    el.style.color = valor >= 0 ? '#2A9D8F' : '#E76F51';
}

// ── Remoção de registro ───────────────────────────────────
function removeRegistro(codigoParaRemover) {
    fetch('deletar_transacao.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ codigo: codigoParaRemover })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            transacoes = transacoes.filter(t => String(t.codigo) !== String(codigoParaRemover));
            renderTable();
            showToast('success', 'Registro removido com sucesso!');
        } else {
            if (data.redirect) {
                showToast('warn', 'Sessão expirada. Faça login novamente.');
                window.location.href = data.redirect;
                return;
            }
            showToast('error', data.error || 'Erro ao remover.');
        }
    })
    .catch(() => showToast('error', 'Erro ao conectar com o servidor.'));
}

document.querySelector('.tabela-extrato tbody').addEventListener('click', async (e) => {
    if (e.target.classList.contains('btn-remove')) {
        const codigo = e.target.dataset.codeRem;
        const confirmado = await showConfirm('Atenção!', 'Tem certeza que deseja remover este registro permanentemente?');
        if (confirmado) removeRegistro(codigo);
    }
});

// ── Carregamento inicial ──────────────────────────────────
function loadTransactions() {
    fetch('get_transacoes.php', { method: 'GET', credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            transacoes = data.transacoes.map(t => ({
                codigo:    t.codigo,
                data:      t.data,
                descricao: t.descricao,
                categoria: t.categoria,
                tipo:      t.tipo,
                valor:     t.valor
            }));
            renderTable();
        } else {
            if (data.redirect) { window.location.href = data.redirect; return; }
            console.error('Erro ao carregar transações:', data.error);
            renderTable();
        }
    })
    .catch(err => { console.error('Erro ao conectar:', err); renderTable(); });
}

loadTransactions();

// ── Logout ────────────────────────────────────────────────
document.getElementById('btn-logout').addEventListener('click', function (e) {
    e.preventDefault();
    fetch('logout.php', { method: 'POST', credentials: 'same-origin' })
    .then(() => { localStorage.removeItem('currentUserName'); window.location.href = 'index.html'; })
    .catch(() => { localStorage.removeItem('currentUserName'); window.location.href = 'index.html'; });
});
