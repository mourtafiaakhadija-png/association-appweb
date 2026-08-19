<?php

if (!isset($_SESSION['benevole_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Vérification que le compte est toujours actif : l'admin a pu le désactiver
// APRÈS que la session ait déjà été ouverte, donc on revérifie à chaque page.
$stmtStatut = $pdo->prepare("SELECT statut FROM users WHERE id = ?");
$stmtStatut->execute([$_SESSION['benevole_id']]);
$statutActuel = $stmtStatut->fetchColumn();

if ($statutActuel !== 'actif') {
    session_unset();
    session_destroy();
    header('Location: login.php?desactive=1');
    exit;
}