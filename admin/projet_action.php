<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$currentUserId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: projets.php');
    exit;
}

verifierJetonCsrf();
$id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$isEdit = $id !== null;

$titre = trim($_POST['titre']);
$categorieId = $_POST['categorie_id'] !== '' ? (int) $_POST['categorie_id'] : null;
$responsableId = $_POST['responsable_id'] !== '' ? (int) $_POST['responsable_id'] : null;
$cibleType = $_POST['cible_type'];
$cibleDetails = trim($_POST['cible_details'] ?? '');
$statut = $_POST['statut'];

try {
    if ($isEdit) {
        $pdo->prepare(
            "UPDATE projets SET titre=?, categorie_id=?, responsable_id=?, cible_type=?, cible_details=?, statut=? WHERE id=?"
        )->execute([$titre, $categorieId, $responsableId, $cibleType, $cibleDetails, $statut, $id]);

        $pdo->prepare(
            "INSERT INTO historique_projets (projet_id, description_action, auteur_id) VALUES (?, ?, ?)"
        )->execute([$id, "Modification de la fiche projet", $currentUserId]);

    } else {
        // description/budget vides par défaut : on garde ces colonnes en base pour compatibilité,
        // mais elles ne sont plus utilisées — le vrai contenu vient des éditions
        $pdo->prepare(
            "INSERT INTO projets (titre, description, categorie_id, responsable_id, cible_type, cible_details, budget_prevu, statut) 
             VALUES (?, '', ?, ?, ?, ?, 0, ?)"
        )->execute([$titre, $categorieId, $responsableId, $cibleType, $cibleDetails, $statut]);
        $id = $pdo->lastInsertId();

        $pdo->prepare(
            "INSERT INTO historique_projets (projet_id, description_action, auteur_id) VALUES (?, ?, ?)"
        )->execute([$id, "Création du projet", $currentUserId]);
    }

    header('Location: projet_editions.php?projet_id=' . $id);
    exit;

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    gererErreur($e, "حدث خطأ أثناء العملية. يرجى المحاولة مرة أخرى أو التواصل مع الإدارة.");
}