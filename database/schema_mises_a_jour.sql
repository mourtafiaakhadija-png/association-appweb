-- Table des mises à jour (évolutions) d'une édition : chaque nouvelle entrée s'ajoute
-- sans jamais écraser la description initiale ni les mises à jour précédentes.
CREATE TABLE IF NOT EXISTS mises_a_jour_edition (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edition_id INT NOT NULL,
    contenu TEXT NOT NULL,
    statut ENUM('en_attente', 'a_corriger', 'validee') NOT NULL DEFAULT 'en_attente',
    commentaire_admin TEXT DEFAULT NULL,
    auteur_id INT NOT NULL,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_validation DATETIME DEFAULT NULL,
    FOREIGN KEY (edition_id) REFERENCES projet_editions(id) ON DELETE CASCADE,
    FOREIGN KEY (auteur_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- On rattache chaque photo soit à la description initiale (maj_id = NULL),
-- soit à une mise à jour précise (maj_id renseigné)
ALTER TABLE photos_projets
    ADD COLUMN maj_id INT DEFAULT NULL AFTER edition_id,
    ADD FOREIGN KEY (maj_id) REFERENCES mises_a_jour_edition(id) ON DELETE CASCADE;