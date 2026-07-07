SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- 1) users (admin, bureau, benevole...)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'password_hash()',
    role ENUM('admin','bureau','benevole','donateur','collaborateur') NOT NULL DEFAULT 'benevole',
    telephone VARCHAR(30) DEFAULT NULL,
    statut ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 2) bureau_membres
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS bureau_membres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fonction VARCHAR(150) NOT NULL COMMENT 'Président, Trésorier, Secrétaire...',
    photo VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 3) candidatures_benevoles 
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS candidatures_benevoles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(30) DEFAULT NULL,
    date_naissance DATE DEFAULT NULL,
    ville VARCHAR(100) DEFAULT NULL,
    profession VARCHAR(150) DEFAULT NULL,
    niveau_etude VARCHAR(150) DEFAULT NULL,
    competences TEXT DEFAULT NULL,
    experiences TEXT DEFAULT NULL,
    motivation TEXT NOT NULL,
    statut ENUM('en_attente','acceptee','rejetee') NOT NULL DEFAULT 'en_attente',
    date_candidature DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_reponse DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 4) categories_projets
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories_projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 5) projets
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    categorie_id INT DEFAULT NULL,
    responsable_id INT DEFAULT NULL COMMENT 'FK vers users (bureau ou benevole)',
    cible_type ENUM('famille','village','ecole','orphelin') NOT NULL,
    cible_details TEXT DEFAULT NULL,
    budget_prevu DECIMAL(12,2) NOT NULL DEFAULT 0,
    budget_collecte DECIMAL(12,2) NOT NULL DEFAULT 0,
    date_debut DATE DEFAULT NULL,
    date_fin DATE DEFAULT NULL,
    statut ENUM('en_cours','termine','suspendu') NOT NULL DEFAULT 'en_cours',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories_projets(id) ON DELETE SET NULL,
    FOREIGN KEY (responsable_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 6) photos_projets
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS photos_projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 7) historique_projets — traçabilité / rapports
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS historique_projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    description_action TEXT NOT NULL,
    auteur_id INT DEFAULT NULL,
    date_action DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 8) dons
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS dons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    nom_donateur VARCHAR(150) NOT NULL,
    email_donateur VARCHAR(150) DEFAULT NULL,
    montant DECIMAL(12,2) NOT NULL,
    date_don DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mode_paiement VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 9) messages_contact
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages_contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    sujet VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    traite BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 10) collaborateurs
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS collaborateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
