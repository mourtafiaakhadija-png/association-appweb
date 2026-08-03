CREATE TABLE IF NOT EXISTS login_attempts (
    email VARCHAR(150) NOT NULL PRIMARY KEY,
    tentatives INT NOT NULL DEFAULT 0,
    derniere_tentative DATETIME NOT NULL,
    bloque_jusqua DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;