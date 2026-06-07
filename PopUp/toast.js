// ── Toast Cartão ─────────────────────────────────────────
function showToast(tipo, titulo) {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      document.body.appendChild(container);
    }
  
    const icones = { success: '✔', error: '✖', warn: '⚠' };
    const agora = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  
    const toast = document.createElement('div');
    toast.className = `card-toast toast-${tipo}`;
    toast.innerHTML = `
      <div class="card-toast-top">
        <div class="card-toast-icon">${icones[tipo]}</div>
        <div>
          <div class="card-toast-label">Finance Easy</div>
          ${titulo}
        </div>
      </div>
      <div class="card-toast-bottom">
        <span>Abraços STR Brasil</span>
        <span>${agora}</span>
      </div>
    `;
  
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
  }


  // ── Confirm Customizado ──────────────────────────────────
  function showConfirm(titulo, mensagem) {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';

        const agora = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

        const confirmBox = document.createElement('div');
        confirmBox.className = 'confirm-box';

        confirmBox.innerHTML = `
            <div class="confirm-header">
                <div class="confirm-icon">❓</div>
                <p class="confirm-title">${titulo}</p>
                <p class="confirm-message">${mensagem}</p>
            </div>
            <div class="confirm-actions">
                <button class="confirm-btn confirm-btn-cancel" id="btn-confirm-cancel">Cancelar</button>
                <button class="confirm-btn confirm-btn-ok"     id="btn-confirm-ok">Confirmar</button>
            </div>
            <div class="confirm-footer">
                <span>Finance Easy</span>
                <span>${agora}</span>
            </div>
        `;

        overlay.appendChild(confirmBox);
        document.body.appendChild(overlay);

        confirmBox.querySelector('#btn-confirm-cancel').addEventListener('click', () => {
            overlay.remove();
            resolve(false);
        });

        confirmBox.querySelector('#btn-confirm-ok').addEventListener('click', () => {
            overlay.remove();
            resolve(true);
        });
    });
}