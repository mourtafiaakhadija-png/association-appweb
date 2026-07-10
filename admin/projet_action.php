<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../includes/upload_helper.php';

$currentUserId = $_SESSION['user_id'];

// --- Suppression d'une photo spécifique (lien direct depuis projet_form.php) ---
if (isset($_GET['delete_photo'])) {
    $photoId = (int) $_GET['delete_photo'];
    $projetId = (int) $_GET['projet_id'];
    $pdo->prepare("DELETE FROM photos_projets WHERE id = ?")->execute([$photoId]);
    header('Location: projet_form.php?id=' . $projetId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: projets.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$isEdit = $id !== null;

$titre = trim($_POST['titre']);
$description = trim($_POST['description']);
$categorieId = $_POST['categorie_id'] !== '' ? (int) $_POST['categorie_id'] : null;
$responsableId = $_POST['responsable_id'] !== '' ? (int) $_POST['responsable_id'] : null;
$cibleType = $_POST['cible_type'];
$cibleDetails = trim($_POST['cible_details'] ?? '');
$budgetPrevu = (float) $_POST['budget_prevu'];
$budgetCollecte = (float) ($_POST['budget_collecte'] ?? 0);
$dateDebut = $_POST['date_debut'] ?: null;
$dateFin = $_POST['date_fin'] ?: null;
$statut = $_POST['statut'];

try {
    $pdo->beginTransaction();

    if ($isEdit) {
        // On récupère l'ancien statut/budget pour savoir quoi noter dans l'historique
        $old = $pdo->prepare("SELECT statut, budget_collecte FROM projets WHERE id = ?");
        $old->execute([$id]);
        $oldData = $old->fetch();

        $pdo->prepare(
            "UPDATE projets SET titre=?, description=?, categorie_id=?, responsable_id=?, cible_type=?, 
             cible_details=?, budget_prevu=?, budget_collecte=?, date_debut=?, date_fin=?, statut=? WHERE id=?"
        )->execute([
            $titre, $description, $categorieId, $responsableId, $cibleType,
            $cibleDetails, $budgetPrevu, $budgetCollecte, $dateDebut, $dateFin, $statut, $id
        ]);

        $action = "Modification du projet (statut: $statut, budget collecté: $budgetCollecte MAD)";
        if ($oldData && $oldData['statut'] !== $statut) {
            $action = "Changement de statut : {$oldData['statut']} → $statut";
        }
        $pdo->prepare(
            "INSERT INTO historique_projets (projet_id, description_action, auteur_id) VALUES (?, ?, ?)"
        )->execute([$id, $action, $currentUserId]);

    } else {
        $pdo->prepare(
            "INSERT INTO projets (titre, description, categorie_id, responsable_id, cible_type, cible_details, 
             budget_prevu, budget_collecte, date_debut, date_fin, statut) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            $titre, $description, $categorieId, $responsableId, $cibleType,
            $cibleDetails, $budgetPrevu, $budgetCollecte, $dateDebut, $dateFin, $statut
        ]);
        $id = $pdo->lastInsertId();

        $pdo->prepare(
            "INSERT INTO historique_projets (projet_id, description_action, auteur_id) VALUES (?, ?, ?)"
        )->execute([$id, "Création du projet", $currentUserId]);
    }

    // --- Upload des photos multiples ---
    if (!empty($_FILES['photos']['name'][0])) {
        $count = count($_FILES['photos']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $singleFile = [
                'name' => $_FILES['photos']['name'][$i],
                'type' => $_FILES['photos']['type'][$i],
                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                'error' => $_FILES['photos']['error'][$i],
                'size' => $_FILES['photos']['size'][$i],
            ];
            $_FILES['single_temp'] = $singleFile;
            $filename = handleImageUpload('single_temp');

            if ($filename !== null) {
                $pdo->prepare(
                    "INSERT INTO photos_projets (projet_id, url) VALUES (?, ?)"
                )->execute([$id, $filename]);
            }
        }
    }

    $pdo->commit();
    header('Location: projets.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur : " . htmlspecialchars($e->getMessage()));
}
