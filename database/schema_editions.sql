
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- 1) projet_editions — chaque édition/version d'un projet
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS projet_editions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    numero_edition INT NOT NULL DEFAULT 1 COMMENT 'ex: 1, 2, 3... ou année comme 2024, 2025',
    description TEXT NOT NULL,
    budget_prevu DECIMAL(12,2) NOT NULL DEFAULT 0,
    budget_collecte DECIMAL(12,2) NOT NULL DEFAULT 0,
    date_debut DATE DEFAULT NULL,
    date_fin DATE DEFAULT NULL,
    statut ENUM('brouillon','en_attente_validation','a_corriger','validee') NOT NULL DEFAULT 'brouillon',
    commentaire_admin TEXT DEFAULT NULL COMMENT 'Rempli si admin renvoie pour correction',
    fichier_rapport VARCHAR(255) DEFAULT NULL COMMENT 'Chemin du fichier Word/PDF uploadé par le bénévole',
    a_la_une BOOLEAN NOT NULL DEFAULT FALSE,
    appel_benevoles_ouvert BOOLEAN NOT NULL DEFAULT FALSE,
    cree_par INT DEFAULT NULL COMMENT 'FK vers users (le bénévole qui a créé cette édition)',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_validation DATETIME DEFAULT NULL,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (cree_par) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 2) photos_projets — on rattache chaque photo à une édition précise
-- ---------------------------------------------------------
ALTER TABLE photos_projets 
    ADD COLUMN edition_id INT DEFAULT NULL AFTER projet_id,
    ADD FOREIGN KEY (edition_id) REFERENCES projet_editions(id) ON DELETE CASCADE;

-- ---------------------------------------------------------
-- 3) dons — on rattache chaque don à une édition précise
--    (le budget_collecte d'une édition se calcule à partir de ça)
-- ---------------------------------------------------------
ALTER TABLE dons 
    ADD COLUMN edition_id INT DEFAULT NULL AFTER projet_id,
    ADD FOREIGN KEY (edition_id) REFERENCES projet_editions(id) ON DELETE CASCADE;

-- ---------------------------------------------------------
-- 4) participations_comite — qui est disponible / confirmé pour quelle édition
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS participations_comite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edition_id INT NOT NULL,
    user_id INT NOT NULL COMMENT 'FK vers users (le bénévole)',
    statut ENUM('disponible','confirme') NOT NULL DEFAULT 'disponible',
    date_reponse DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (edition_id) REFERENCES projet_editions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (edition_id, user_id) COMMENT 'Un bénévole ne peut se déclarer disponible qu''une fois par édition'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- MIGRATION DES DONNÉES EXISTANTES
-- Chaque projet actuel devient automatiquement son "Édition 1"
-- avec sa description/budget/dates actuels — AUCUNE DONNÉE PERDUE
-- ===========================================================

INSERT INTO projet_editions (projet_id, numero_edition, description, budget_prevu, budget_collecte, date_debut, date_fin, statut, date_creation)
SELECT id, 1, description, budget_prevu, budget_collecte, date_debut, date_fin, 'validee', created_at
FROM projets;

-- Rattacher les photos existantes à cette "Édition 1" nouvellement créée
UPDATE photos_projets pp
JOIN projet_editions pe ON pe.projet_id = pp.projet_id AND pe.numero_edition = 1
SET pp.edition_id = pe.id;

-- Rattacher les dons existants à cette "Édition 1" nouvellement créée
UPDATE dons d
JOIN projet_editions pe ON pe.projet_id = d.projet_id AND pe.numero_edition = 1
SET d.edition_id = pe.id;

SET FOREIGN_KEY_CHECKS = 1;
