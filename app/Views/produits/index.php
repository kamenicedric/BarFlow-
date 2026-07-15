<?= ui_page_header('Gestion produits', 'Catalogue, prix et stock critique en une vue') ?>

<form method="POST" action="<?= url('/produits') ?>" class="card card-body mb-4 form-surface">
    <?= csrf_field() ?>
    <div class="row g-2">
        <div class="col-md-3"><input class="form-control" name="nom" placeholder="Nom" required></div>
        <div class="col-md-2"><input class="form-control" name="prix_achat" type="number" step="0.01" placeholder="Prix achat" required></div>
        <div class="col-md-2"><input class="form-control" name="prix_vente" type="number" step="0.01" placeholder="Prix vente" required></div>
        <div class="col-md-1"><input class="form-control" name="stock" type="number" step="0.01" placeholder="Stock" required></div>
        <div class="col-md-1"><input class="form-control" name="stock_critique" type="number" step="0.01" placeholder="Seuil" required></div>
        <div class="col-md-2"><input class="form-control" name="unite" placeholder="Unite" required></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">+</button></div>
    </div>
</form>

<div class="card">
    <div class="card-body">
        <input id="searchProduit" class="form-control mb-3" placeholder="Recherche instantanee produit/code barre">
        <div class="table-responsive">
            <table class="table table-striped align-middle" id="tableProduits">
                <thead><tr><th>ID</th><th>Nom</th><th>Prix vente</th><th>Stock</th><th>Seuil</th><th>Unite</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($produits as $p): ?>
                    <tr>
                        <td><?= (int) $p['id'] ?></td>
                        <td><?= e($p['nom']) ?></td>
                        <td><?= number_format((float) $p['prix_vente'], 0, ',', ' ') ?></td>
                        <td><?= e((string) $p['stock']) ?></td>
                        <td><?= e((string) $p['stock_critique']) ?></td>
                        <td><?= e($p['unite']) ?></td>
                        <td>
                            <form method="POST" action="<?= url('/produits/delete') ?>" onsubmit="return confirm('Supprimer ce produit ?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger">Soft delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= url('/assets/js/produits.js') ?>"></script>
