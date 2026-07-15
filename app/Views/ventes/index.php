<?= ui_page_header('Point de vente tactile', 'Encaissement rapide optimise pour tablette 8-10 pouces') ?>

<div class="row g-3 pos-layout">
    <div class="col-lg-8">
        <div class="card pos-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Caisse tactile</span>
                <span class="badge text-bg-primary">Mode rapide</span>
            </div>
            <div class="card-body">
                <div class="pos-filter-row mb-3">
                    <button class="btn btn-outline-primary btn-sm">Tout</button>
                    <button class="btn btn-outline-secondary btn-sm">Bieres</button>
                    <button class="btn btn-outline-secondary btn-sm">Soft</button>
                    <button class="btn btn-outline-secondary btn-sm">Snacks</button>
                </div>
                <div class="pos-quick-actions mb-3">
                    <span class="text-muted small">Ajout rapide:</span>
                    <button type="button" class="btn btn-sm btn-outline-primary js-quick-qty" data-qty="1">+1</button>
                    <button type="button" class="btn btn-sm btn-outline-primary js-quick-qty" data-qty="2">+2</button>
                    <button type="button" class="btn btn-sm btn-outline-primary js-quick-qty" data-qty="5">+5</button>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="openNumpad">
                        <i class="bi bi-grid-3x3-gap"></i> Clavier
                    </button>
                    <span class="ms-auto small text-muted">Produit cible: <strong id="activeProductName">Aucun</strong></span>
                </div>
                <div class="row g-2" id="produits-grid">
                    <?php foreach ($produits as $produit): ?>
                        <div class="col-6 col-md-4 col-xl-3">
                            <button type="button" class="btn btn-outline-primary w-100 py-3 js-add-item pos-product-btn"
                                    data-id="<?= (int) $produit['id'] ?>"
                                    data-nom="<?= e($produit['nom']) ?>"
                                    data-prix="<?= (float) $produit['prix_vente'] ?>">
                                <div><?= e($produit['nom']) ?></div>
                                <small><?= number_format((float) $produit['prix_vente'], 0, ',', ' ') ?> FCFA</small>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card sticky-top pos-summary-card" style="top:1rem;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Panier</span>
                <span class="badge text-bg-dark">Session active</span>
            </div>
            <div class="card-body">
                <ul id="cart-list" class="list-group mb-3"></ul>
                <h5 class="mb-3">Total: <span id="cart-total">0</span> FCFA</h5>
                <div class="d-grid gap-2 mb-3">
                    <button type="button" class="btn btn-outline-secondary" id="printTicketBtn">
                        <i class="bi bi-printer"></i> Imprimer ticket
                    </button>
                </div>
                <form id="sale-form">
                    <?= csrf_field() ?>
                    <label class="form-label">Mode paiement</label>
                    <select id="mode_paiement" class="form-select mb-2">
                        <option value="especes">Especes</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="carte">Carte bancaire</option>
                    </select>
                    <button class="btn btn-success w-100 btn-lg" type="submit"><i class="bi bi-check-circle"></i> Valider vente</button>
                </form>
                <div id="sale-feedback" class="small mt-2"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="numpadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clavier numerique</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="numpad-display mb-3" id="numpadDisplay">0</div>
                <div class="numpad-grid">
                    <?php foreach ([1,2,3,4,5,6,7,8,9,0] as $digit): ?>
                        <button type="button" class="btn btn-outline-primary js-numpad-key" data-key="<?= $digit ?>"><?= $digit ?></button>
                    <?php endforeach; ?>
                    <button type="button" class="btn btn-outline-secondary js-numpad-key" data-key="clear">C</button>
                    <button type="button" class="btn btn-primary js-numpad-key" data-key="ok">OK</button>
                </div>
                <p class="small text-muted mt-3 mb-0">Selectionne d'abord un produit puis saisis une quantite.</p>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('/assets/js/ventes.js') ?>"></script>
