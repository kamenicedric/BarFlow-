# Documentation technique - BarFlow

## Architecture

BarFlow suit une architecture MVC personnalisee:

- Front Controller: `public/index.php`
- Routage: `app/Core/Router.php` + `routes/web.php`
- Middlewares: `app/Middleware/*`
- Controleurs: `app/Controllers/*`
- Modeles: `app/Models/*`
- Vues: `app/Views/*`

## Flux d'une requete

1. Apache redirige vers `public/index.php`
2. Initialisation Dotenv + session securisee
3. Chargement des routes
4. Resolution route + execution middlewares
5. Appel du controleur
6. Reponse HTML ou JSON (AJAX)

## Transactions metier

Les ventes utilisent une transaction SQL:

- Verification stock
- Creation vente + details
- Mise a jour stock produit
- Insertion mouvement de stock
- Commit ou rollback en cas d'echec

## Regles metier appliquees

- Pas de vente si stock insuffisant
- Chaque vente genere un mouvement de stock
- Fermeture caisse calcule l'ecart theorique/reel
- Soft delete sur entites principales
- Audit des actions critiques

## API AJAX actuelle

- `GET /api/dashboard/stats`
- `GET /api/produits/search?q=...`
- `GET /api/stock/alerts`
- `POST /ventes` (enregistrement vente)

## Convention de code

- POO stricte
- Separation responsabilites (SOLID/DRY)
- Validation serveur avant persistence
- Protection contre injections SQL via PDO prepares

## Points d'extension

- Service classes metier (ex: `StockService`, `ReportService`)
- Export PDF/Excel centralise
- Permissions fines par action (policy)
- API REST versionnee (`/api/v1`)
- Multi-etablissements (multi-bar)
