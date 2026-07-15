-- BarFlow migration 002 : ameliorations securite + fonctionnalites
-- Idempotent autant que possible (a executer une fois).
SET NAMES utf8mb4;

-- Reinitialisation de mot de passe par token securise
CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NULL,
    INDEX idx_pr_user(user_id),
    INDEX idx_pr_token(token_hash),
    INDEX idx_pr_expires(expires_at),
    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Tokens "se souvenir de moi" securises (haches)
CREATE TABLE IF NOT EXISTS user_remember_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NULL,
    INDEX idx_rt_user(user_id),
    INDEX idx_rt_token(token_hash),
    INDEX idx_rt_expires(expires_at),
    CONSTRAINT fk_rt_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Conversions produits (ex: casier -> bouteille, bouteille -> verre)
-- unite_achat: unite lors de l'approvisionnement
-- facteur_conversion: nombre d'unites de vente contenues dans 1 unite d'achat
ALTER TABLE produits
    ADD COLUMN unite_achat VARCHAR(30) NULL AFTER unite;
ALTER TABLE produits
    ADD COLUMN facteur_conversion DECIMAL(12,3) NOT NULL DEFAULT 1 AFTER unite_achat;
