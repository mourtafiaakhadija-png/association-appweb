ALTER TABLE projet_editions
    ADD COLUMN rapport_statut ENUM('non_envoye','en_attente','a_corriger','valide') NOT NULL DEFAULT 'non_envoye' AFTER fichier_rapport,
    ADD COLUMN commentaire_rapport TEXT DEFAULT NULL COMMENT 'Rempli par admin si le rapport est renvoyé pour correction' AFTER rapport_statut;

-- Les rapports déjà uploadés avant cette mise à jour : on les marque "en_attente" pour que l'admin les vérifie
UPDATE projet_editions SET rapport_statut = 'en_attente' WHERE fichier_rapport IS NOT NULL;