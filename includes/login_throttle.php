<?php
/**
 * Protection contre les tentatives de connexion en boucle (brute force).
 * Après 5 échecs, l'email est bloqué 15 minutes.
 */

const MAX_TENTATIVES = 5;
const DUREE_BLOCAGE_MINUTES = 15;

/**
 * Vérifie si cet email est actuellement bloqué.
 * Retourne le nombre de minutes restantes si bloqué, ou 0 si non bloqué.
 */
function estBloque(PDO $pdo, string $email): int
{
    $stmt = $pdo->prepare("SELECT bloque_jusqua FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
    $bloqueJusqua = $stmt->fetchColumn();

    if ($bloqueJusqua && strtotime($bloqueJusqua) > time()) {
        return (int) ceil((strtotime($bloqueJusqua) - time()) / 60);
    }
    return 0;
}

/**
 * Enregistre une tentative échouée. Bloque l'email si le seuil est dépassé.
 */
function enregistrerEchec(PDO $pdo, string $email): void
{
    $stmt = $pdo->prepare("SELECT tentatives FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
    $tentatives = (int) $stmt->fetchColumn();
    $tentatives++;

    $bloqueJusqua = null;
    if ($tentatives >= MAX_TENTATIVES) {
        $bloqueJusqua = date('Y-m-d H:i:s', time() + DUREE_BLOCAGE_MINUTES * 60);
    }

    $pdo->prepare(
        "INSERT INTO login_attempts (email, tentatives, derniere_tentative, bloque_jusqua) VALUES (?, ?, NOW(), ?)
         ON DUPLICATE KEY UPDATE tentatives = ?, derniere_tentative = NOW(), bloque_jusqua = ?"
    )->execute([$email, $tentatives, $bloqueJusqua, $tentatives, $bloqueJusqua]);
}

/**
 * Remet le compteur à zéro après une connexion réussie.
 */
function reinitialiserTentatives(PDO $pdo, string $email): void
{
    $pdo->prepare("DELETE FROM login_attempts WHERE email = ?")->execute([$email]);
}