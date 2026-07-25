<?php
session_start();
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';

$benevoleId = $_SESSION['benevole_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: appels_benevoles.php');
    exit;
}

$editionId = (int) ($_POST['edition_id'] ?? 0);

// Vérifie que cette édition est bien un appel à bénévoles ouvert et publié
$stmt = $pdo->prepare("SELECT id FROM projet_editions WHERE id = ? AND statut = 'validee' AND appel_benevoles_ouvert = 1");
$stmt->execute([$editionId]);

if ($stmt->fetch()) {
    // INSERT IGNORE : la contrainte UNIQUE (edition_id, user_id) empêche un doublon si le bénévole
    // clique deux fois par erreur — pas besoin de vérifier "existe déjà" à la main avant
    $pdo->prepare(
        "INSERT IGNORE INTO participations_comite (edition_id, user_id, statut) VALUES (?, ?, 'disponible')"
    )->execute([$editionId, $benevoleId]);
}

header('Location: appels_benevoles.php');
exit;