const cart = new Map();
const list = document.getElementById('cart-list');
const totalEl = document.getElementById('cart-total');
const feedback = document.getElementById('sale-feedback');
const activeProductLabel = document.getElementById('activeProductName');
const quickQtyButtons = document.querySelectorAll('.js-quick-qty');
const numpadModalEl = document.getElementById('numpadModal');
const numpadDisplay = document.getElementById('numpadDisplay');
const printTicketBtn = document.getElementById('printTicketBtn');

const numpadModal = numpadModalEl ? new bootstrap.Modal(numpadModalEl) : null;
let activeProductId = null;
let numpadValue = '';

function playSuccessSound() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const oscillator = ctx.createOscillator();
        const gain = ctx.createGain();
        oscillator.type = 'sine';
        oscillator.frequency.value = 880;
        gain.gain.value = 0.08;
        oscillator.connect(gain);
        gain.connect(ctx.destination);
        oscillator.start();
        oscillator.stop(ctx.currentTime + 0.12);
    } catch (_) {
        // no-op si audio indisponible
    }
}

function addToCart(productId, productName, price, quantity = 1) {
    const item = cart.get(productId) || {
        produit_id: productId,
        nom: productName,
        prix: Number(price),
        quantite: 0
    };

    item.quantite = Math.max(0, Number(item.quantite) + Number(quantity));
    if (item.quantite <= 0) {
        cart.delete(productId);
    } else {
        cart.set(productId, item);
    }
    renderCart();
}

function setActiveProduct(button) {
    document.querySelectorAll('.js-add-item').forEach((el) => el.classList.remove('active-product'));
    button.classList.add('active-product');
    activeProductId = Number(button.dataset.id);
    if (activeProductLabel) {
        activeProductLabel.textContent = button.dataset.nom || 'Aucun';
    }
}

function renderCart() {
    list.innerHTML = '';
    let total = 0;

    cart.forEach((item) => {
        total += item.prix * item.quantite;
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center cart-item';
        li.innerHTML = `
            <div>
                <strong>${item.nom}</strong>
                <div class="small text-muted">x${item.quantite} - ${BarFlow.formatMoney(item.prix)} FCFA</div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary js-cart-minus" data-id="${item.produit_id}">-</button>
                <button type="button" class="btn btn-sm btn-outline-secondary js-cart-plus" data-id="${item.produit_id}">+</button>
                <button type="button" class="btn btn-sm btn-outline-danger js-cart-remove" data-id="${item.produit_id}"><i class="bi bi-x"></i></button>
            </div>
        `;
        list.appendChild(li);
    });

    totalEl.textContent = BarFlow.formatMoney(total);
}

function getProductButtonById(id) {
    return document.querySelector(`.js-add-item[data-id="${id}"]`);
}

document.querySelectorAll('.js-add-item').forEach((button) => {
    button.addEventListener('click', () => {
        setActiveProduct(button);
        addToCart(
            Number(button.dataset.id),
            button.dataset.nom,
            Number(button.dataset.prix),
            1
        );
    });
});

quickQtyButtons.forEach((button) => {
    button.addEventListener('click', () => {
        if (!activeProductId) {
            feedback.textContent = 'Selectionne un produit avant l ajout rapide';
            feedback.className = 'text-danger';
            return;
        }
        const productButton = getProductButtonById(activeProductId);
        if (!productButton) return;
        const qty = Number(button.dataset.qty || 1);
        addToCart(activeProductId, productButton.dataset.nom, productButton.dataset.prix, qty);
    });
});

list.addEventListener('click', (event) => {
    const target = event.target.closest('button');
    if (!target) return;
    const id = Number(target.dataset.id);
    const item = cart.get(id);
    if (!item) return;

    if (target.classList.contains('js-cart-minus')) {
        addToCart(id, item.nom, item.prix, -1);
    } else if (target.classList.contains('js-cart-plus')) {
        addToCart(id, item.nom, item.prix, 1);
    } else if (target.classList.contains('js-cart-remove')) {
        cart.delete(id);
        renderCart();
    }
});

document.getElementById('openNumpad')?.addEventListener('click', () => {
    if (!activeProductId) {
        feedback.textContent = 'Selectionne un produit pour ouvrir le clavier';
        feedback.className = 'text-danger';
        return;
    }
    numpadValue = '';
    if (numpadDisplay) numpadDisplay.textContent = '0';
    numpadModal?.show();
});

document.querySelectorAll('.js-numpad-key').forEach((keyButton) => {
    keyButton.addEventListener('click', () => {
        const key = keyButton.dataset.key;
        if (key === 'clear') {
            numpadValue = '';
            numpadDisplay.textContent = '0';
            return;
        }

        if (key === 'ok') {
            const qty = Number(numpadValue || 0);
            if (qty <= 0 || !activeProductId) {
                numpadModal?.hide();
                return;
            }
            const productButton = getProductButtonById(activeProductId);
            if (productButton) {
                addToCart(activeProductId, productButton.dataset.nom, productButton.dataset.prix, qty);
            }
            numpadModal?.hide();
            return;
        }

        numpadValue = `${numpadValue}${key}`.slice(0, 3);
        numpadDisplay.textContent = numpadValue || '0';
    });
});

function printTicket(payload) {
    const content = `
        <html>
        <head>
            <title>Ticket Vente #${payload.vente_id}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 12px; }
                h3 { margin: 0 0 8px; }
                .line { display: flex; justify-content: space-between; margin: 4px 0; }
                .total { font-weight: bold; margin-top: 12px; border-top: 1px dashed #000; padding-top: 8px; }
            </style>
        </head>
        <body>
            <h3>BarFlow - Ticket</h3>
            <p>#${payload.vente_id} | ${new Date().toLocaleString('fr-FR')}</p>
            ${(payload.items || []).map(item => `
                <div class="line"><span>${item.nom} x${item.quantite}</span><span>${BarFlow.formatMoney(item.prix * item.quantite)} FCFA</span></div>
            `).join('')}
            <div class="line total"><span>Total</span><span>${BarFlow.formatMoney(payload.total)} FCFA</span></div>
        </body>
        </html>
    `;

    const ticketWindow = window.open('', '_blank', 'width=360,height=600');
    if (!ticketWindow) return;
    ticketWindow.document.write(content);
    ticketWindow.document.close();
    ticketWindow.focus();
    ticketWindow.print();
}

let lastSalePayload = null;

printTicketBtn?.addEventListener('click', () => {
    if (!lastSalePayload) {
        feedback.textContent = 'Aucun ticket disponible. Valide une vente d abord.';
        feedback.className = 'text-danger';
        return;
    }
    printTicket(lastSalePayload);
});

document.getElementById('sale-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!cart.size) {
        feedback.textContent = 'Panier vide';
        feedback.className = 'text-danger';
        return;
    }

    const cartSnapshot = Array.from(cart.values()).map((item) => ({ ...item }));
    const formData = new FormData();
    const csrf = document.querySelector('input[name="_csrf"]')?.value || '';
    formData.append('_csrf', csrf);
    formData.append('mode_paiement', document.getElementById('mode_paiement').value);

    cartSnapshot.forEach((item, index) => {
        formData.append(`items[${index}][produit_id]`, String(item.produit_id));
        formData.append(`items[${index}][quantite]`, String(item.quantite));
    });

    const response = await fetch(BarFlow.url('/ventes'), {
        method: 'POST',
        body: formData
    });

    const payload = await response.json();
    if (!response.ok) {
        feedback.textContent = payload.message || 'Erreur';
        feedback.className = 'text-danger';
        return;
    }

    feedback.textContent = `Vente #${payload.vente_id} enregistree (${BarFlow.formatMoney(payload.total)} FCFA)`;
    feedback.className = 'text-success';
    playSuccessSound();

    lastSalePayload = { ...payload, items: cartSnapshot };
    printTicket(lastSalePayload);
    cart.clear();
    renderCart();
});
