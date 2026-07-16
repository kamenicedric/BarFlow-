-- BarFlow migration initiale MySQL 8+
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    nom VARCHAR(120) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(120) NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_users_role(role_id),
    INDEX idx_users_deleted(deleted_at),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    INDEX idx_attempt_username_time(username, attempted_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    status ENUM('success','failed') NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_login_history_user(user_id),
    INDEX idx_login_history_created(created_at),
    CONSTRAINT fk_login_history_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_categories_nom(nom)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS produits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categorie_id BIGINT UNSIGNED NULL,
    nom VARCHAR(120) NOT NULL,
    prix_achat DECIMAL(12,2) NOT NULL,
    prix_vente DECIMAL(12,2) NOT NULL,
    stock DECIMAL(12,3) NOT NULL DEFAULT 0,
    stock_critique DECIMAL(12,3) NOT NULL DEFAULT 0,
    unite VARCHAR(30) NOT NULL,
    code_barre VARCHAR(100) NULL,
    image VARCHAR(255) NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_produits_categorie(categorie_id),
    INDEX idx_produits_nom(nom),
    INDEX idx_produits_codebarre(code_barre),
    INDEX idx_produits_deleted(deleted_at),
    CONSTRAINT fk_produits_categorie FOREIGN KEY (categorie_id) REFERENCES categories(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fournisseurs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    telephone VARCHAR(30) NULL,
    email VARCHAR(120) NULL,
    adresse VARCHAR(255) NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_fournisseurs_nom(nom)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS caisses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_ouverture_id BIGINT UNSIGNED NOT NULL,
    utilisateur_fermeture_id BIGINT UNSIGNED NULL,
    montant_initial DECIMAL(12,2) NOT NULL DEFAULT 0,
    montant_reel DECIMAL(12,2) NULL,
    montant_theorique DECIMAL(12,2) NULL,
    ecart DECIMAL(12,2) NULL,
    date_ouverture DATETIME NOT NULL,
    date_fermeture DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_caisses_ouverture(date_ouverture),
    INDEX idx_caisses_user_open(utilisateur_ouverture_id),
    CONSTRAINT fk_caisses_user_open FOREIGN KEY (utilisateur_ouverture_id) REFERENCES users(id),
    CONSTRAINT fk_caisses_user_close FOREIGN KEY (utilisateur_fermeture_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ventes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    caisse_id BIGINT UNSIGNED NOT NULL,
    utilisateur_id BIGINT UNSIGNED NOT NULL,
    mode_paiement ENUM('especes','mobile_money','carte') NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_ventes_caisse(caisse_id),
    INDEX idx_ventes_user(utilisateur_id),
    INDEX idx_ventes_created(created_at),
    CONSTRAINT fk_ventes_caisse FOREIGN KEY (caisse_id) REFERENCES caisses(id),
    CONSTRAINT fk_ventes_user FOREIGN KEY (utilisateur_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ventes_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vente_id BIGINT UNSIGNED NOT NULL,
    produit_id BIGINT UNSIGNED NOT NULL,
    quantite DECIMAL(12,3) NOT NULL,
    prix_unitaire DECIMAL(12,2) NOT NULL,
    sous_total DECIMAL(12,2) NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_ventes_details_vente(vente_id),
    INDEX idx_ventes_details_produit(produit_id),
    CONSTRAINT fk_ventes_details_vente FOREIGN KEY (vente_id) REFERENCES ventes(id),
    CONSTRAINT fk_ventes_details_produit FOREIGN KEY (produit_id) REFERENCES produits(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS approvisionnements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produit_id BIGINT UNSIGNED NOT NULL,
    fournisseur_id BIGINT UNSIGNED NULL,
    quantite DECIMAL(12,3) NOT NULL,
    prix_total DECIMAL(12,2) NOT NULL,
    facture_path VARCHAR(255) NULL,
    date_approvisionnement DATETIME NOT NULL,
    utilisateur_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_app_date(date_approvisionnement),
    INDEX idx_app_fournisseur(fournisseur_id),
    CONSTRAINT fk_app_produit FOREIGN KEY (produit_id) REFERENCES produits(id),
    CONSTRAINT fk_app_fournisseur FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id),
    CONSTRAINT fk_app_user FOREIGN KEY (utilisateur_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pertes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produit_id BIGINT UNSIGNED NOT NULL,
    responsable_id BIGINT UNSIGNED NULL,
    type_perte ENUM('bouteille_cassee_pleine','bouteille_cassee_vide','vol','erreur_comptage','perte_inconnue') NOT NULL,
    quantite DECIMAL(12,3) NOT NULL,
    valeur_totale DECIMAL(12,2) NOT NULL DEFAULT 0,
    justification TEXT NULL,
    date_perte DATETIME NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_pertes_date(date_perte),
    CONSTRAINT fk_pertes_produit FOREIGN KEY (produit_id) REFERENCES produits(id),
    CONSTRAINT fk_pertes_user FOREIGN KEY (responsable_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produit_id BIGINT UNSIGNED NOT NULL,
    quantite DECIMAL(12,3) NOT NULL,
    raison VARCHAR(255) NOT NULL,
    autorise_par BIGINT UNSIGNED NULL,
    valeur_totale DECIMAL(12,2) NOT NULL DEFAULT 0,
    date_don DATETIME NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_dons_date(date_don),
    CONSTRAINT fk_dons_produit FOREIGN KEY (produit_id) REFERENCES produits(id),
    CONSTRAINT fk_dons_user FOREIGN KEY (autorise_par) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS depenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    categorie ENUM('electricite','eau','salaire','reparation','divers') NOT NULL,
    donneur_ordre VARCHAR(120) NULL,
    executant VARCHAR(120) NULL,
    preuve_path VARCHAR(255) NULL,
    date_depense DATETIME NOT NULL,
    utilisateur_id BIGINT UNSIGNED NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_depenses_date(date_depense),
    INDEX idx_depenses_categorie(categorie),
    CONSTRAINT fk_depenses_user FOREIGN KEY (utilisateur_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mouvements_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produit_id BIGINT UNSIGNED NOT NULL,
    type_mouvement ENUM('vente','approvisionnement','don','perte','correction') NOT NULL,
    quantite DECIMAL(12,3) NOT NULL,
    ancien_stock DECIMAL(12,3) NOT NULL,
    nouveau_stock DECIMAL(12,3) NOT NULL,
    utilisateur_id BIGINT UNSIGNED NULL,
    justification VARCHAR(255) NULL,
    date_mouvement DATETIME NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    INDEX idx_mvt_produit(produit_id),
    INDEX idx_mvt_type(type_mouvement),
    INDEX idx_mvt_date(date_mouvement),
    CONSTRAINT fk_mvt_produit FOREIGN KEY (produit_id) REFERENCES produits(id),
    CONSTRAINT fk_mvt_user FOREIGN KEY (utilisateur_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action_type VARCHAR(80) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id BIGINT NULL,
    old_value JSON NULL,
    new_value JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_audit_user(user_id),
    INDEX idx_audit_table_record(table_name, record_id),
    INDEX idx_audit_created(created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom_bar VARCHAR(120) NOT NULL DEFAULT 'BarFlow',
    logo_path VARCHAR(255) NULL,
    devise VARCHAR(20) NOT NULL DEFAULT 'FCFA',
    taux_tva DECIMAL(5,2) NOT NULL DEFAULT 0,
    seuil_stock_critique_global DECIMAL(12,3) NOT NULL DEFAULT 5,
    sauvegarde_auto TINYINT(1) NOT NULL DEFAULT 1,
    theme VARCHAR(30) NOT NULL DEFAULT 'light',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL
) ENGINE=InnoDB;

INSERT INTO roles (nom, created_at, updated_at)
SELECT 'administrateur', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM roles WHERE nom = 'administrateur');
INSERT INTO roles (nom, created_at, updated_at)
SELECT 'gerant', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM roles WHERE nom = 'gerant');
INSERT INTO roles (nom, created_at, updated_at)
SELECT 'serveuse', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM roles WHERE nom = 'serveuse');

INSERT INTO users (role_id, nom, username, password, email, actif, created_at, updated_at)
SELECT r.id, 'Admin BarFlow', 'admin', '$2y$10$ebtnFQiFuO707ljm853gLOjYIYkYMCV2P6FMInzT5t3mYbC9i0c5.', 'admin@barflow.local', 1, NOW(), NOW()
FROM roles r
WHERE r.nom = 'administrateur'
AND NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

INSERT INTO settings (nom_bar, devise, taux_tva, seuil_stock_critique_global, sauvegarde_auto, theme, created_at, updated_at)
SELECT 'BarFlow', 'FCFA', 0, 5, 1, 'light', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM settings);

SET FOREIGN_KEY_CHECKS = 1;
