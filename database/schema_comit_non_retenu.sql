ALTER TABLE participations_comite 
    MODIFY COLUMN statut ENUM('disponible','confirme','non_retenu') NOT NULL DEFAULT 'disponible';