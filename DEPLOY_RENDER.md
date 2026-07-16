# Deploiement BarFlow sur Render (plan gratuit)

BarFlow se deploie sur **Render** (hebergement web gratuit) avec une **base MySQL externe gratuite** (Render ne propose pas MySQL en gratuit).

## 1. Pousser le code sur GitHub

Le depot doit etre a jour sur GitHub : `https://github.com/kamenicedric/BarFlow-`

```bash
git push origin main
```

## 2. Creer une base MySQL gratuite

Choisis **une** option :

### Option A — TiDB Cloud (recommande, fiable pour portfolio)

1. Va sur [tidbcloud.com](https://tidbcloud.com) → compte gratuit.
2. Cree un cluster **Serverless** (gratuit).
3. Onglet **Connect** → recupere :
   - Host
   - Port (souvent `4000`)
   - User / Password
   - Database name

> TiDB est compatible MySQL. Utilise le port indique par TiDB (pas forcement 3306).

### Option B — db4free.net (plus simple, moins stable)

1. Va sur [db4free.net](https://www.db4free.net) → cree une base.
2. Note host, nom de base, utilisateur et mot de passe.

## 3. Deployer sur Render

1. Va sur [render.com](https://render.com) → connecte-toi avec GitHub.
2. **New +** → **Blueprint**.
3. Selectionne le repo `kamenicedric/BarFlow-`.
4. Render detecte `render.yaml` → **Apply**.
5. Avant le deploy final, renseigne les variables **sync: false** dans le service `barflow` :

| Variable       | Exemple                          |
|----------------|----------------------------------|
| `DB_HOST`      | `gateway01.us-east-1.prod.aws.tidbcloud.com` |
| `DB_PORT`      | `4000` (ou celui fourni)         |
| `DB_DATABASE`  | `barflow`                        |
| `DB_USERNAME`  | ton utilisateur                  |
| `DB_PASSWORD`  | ton mot de passe                 |
| `APP_URL`      | *(laisser vide : auto via Render)* |

6. Lance le deploy et attends 5–10 min (build Docker + Composer + Dompdf).

## 4. Verifier

- Healthcheck : `https://TON-APP.onrender.com/health`  
  Reponse attendue : `{"status":"ok","database":"ok",...}`
- Login : `https://TON-APP.onrender.com/login`  
  Compte par defaut : `admin` / `admin123` → **change le mot de passe tout de suite**.

## 5. Portfolio

Sur ton portfolio, ajoute par exemple :

- **Demo live** : lien `https://barflow-xxxx.onrender.com`
- **Code source** : lien GitHub
- **Stack** : PHP 8, MVC custom, MySQL, Bootstrap 5, Chart.js, Dompdf
- **Fonctionnalites** : POS tactile, stock, caisse, rapports PDF, audit, multi-utilisateurs

> **Note plan gratuit Render** : l'app s'endort apres ~15 min sans visite. La premiere ouverture peut prendre 30–60 s (cold start). Normal pour une demo portfolio.

## Depannage

| Probleme | Solution |
|----------|----------|
| `/health` → `database: error` | Verifie `DB_*` dans Render → Environment |
| 502 au demarrage | Attends la fin du build ; consulte les logs Render |
| Routes 404 | `APP_URL` doit etre l'URL Render sans slash final |
| Uploads / PDF | Dossiers `storage/` et `public/assets/uploads/` sont en ecriture dans l'image Docker |

## Mise a jour

Chaque `git push` sur `main` redeploie automatiquement si `autoDeploy: true` dans `render.yaml`.
