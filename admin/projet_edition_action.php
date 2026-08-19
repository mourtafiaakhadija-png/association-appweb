<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../includes/upload_helper.php';
require_once '../includes/notifications.php';

// --- Suppression d'une photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photo'])) {
    verifierJetonCsrf();
    $photoId = (int) $_POST['delete_photo'];
    $editionId = (int) $_POST['edition_id'];
    $pdo->prepare("DELETE FROM photos_projets WHERE id = ?")->execute([$photoId]);
    header('Location: projet_edition_form.php?id=' . $editionId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: projets.php');
    exit;
}

verifierJetonCsrf();
$id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$isEdit = $id !== null;
$projetId = (int) $_POST['projet_id'];

$numeroEdition = (int) $_POST['numero_edition'];
$description = trim($_POST['description']);
$budgetPrevu = (float) $_POST['budget_prevu'];
$budgetCollecte = (float) ($_POST['budget_collecte'] ?? 0);
$dateDebut = $_POST['date_debut'] ?: null;
$dateFin = $_POST['date_fin'] ?: null;
$statut = $_POST['statut'];
$aLaUne = isset($_POST['a_la_une']) ? 1 : 0;
$appelBenevolesOuvert = isset($_POST['appel_benevoles_ouvert']) ? 1 : 0;

// --- Infos utiles pour décider si on notifie, capturées AVANT la sauvegarde ---
$ancienStatut = null;
if ($isEdit) {
    $stmtAncien = $pdo->prepare("SELECT statut FROM projet_editions WHERE id = ?");
    $stmtAncien->execute([$id]);
    $ancienStatut = $stmtAncien->fetchColumn();
}
$stmtDejaValidee = $pdo->prepare(
    "SELECT COUNT(*) FROM projet_editions WHERE projet_id = ? AND statut = 'validee'" . ($isEdit ? " AND id != ?" : "")
);
$stmtDejaValidee->execute($isEdit ? [$projetId, $id] : [$projetId]);
$projetAvaitDejaUneEditionValidee = (bool) $stmtDejaValidee->fetchColumn();

try {
    $pdo->beginTransaction();

    if ($isEdit) {
        $dateValidationSql = $statut === 'validee' ? ", date_validation = NOW()" : "";

        $pdo->prepare(
            "UPDATE projet_editions SET numero_edition=?, description=?, budget_prevu=?, budget_collecte=?, 
             date_debut=?, date_fin=?, statut=?, a_la_une=?, appel_benevoles_ouvert=?, commentaire_admin=NULL $dateValidationSql 
             WHERE id=?"
        )->execute([
            $numeroEdition, $description, $budgetPrevu, $budgetCollecte,
            $dateDebut, $dateFin, $statut, $aLaUne, $appelBenevolesOuvert, $id
        ]);
    } else {
        $pdo->prepare(
            "INSERT INTO projet_editions 
             (projet_id, numero_edition, description, budget_prevu, budget_collecte, date_debut, date_fin, statut, a_la_une, appel_benevoles_ouvert, cree_par, date_validation) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            $projetId, $numeroEdition, $description, $budgetPrevu, $budgetCollecte,
            $dateDebut, $dateFin, $statut, $aLaUne, $appelBenevolesOuvert,
            $_SESSION['user_id'], $statut === 'validee' ? date('Y-m-d H:i:s') : null
        ]);
        $id = $pdo->lastInsertId();
    }

    // --- Upload des photos multiples, rattachées à cette édition ---
    $photosAjouteesCount = 0;
    if (!empty($_FILES['photos']['name'][0])) {
        $count = count($_FILES['photos']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $_FILES['single_temp'] = [
                'name' => $_FILES['photos']['name'][$i],
                'type' => $_FILES['photos']['type'][$i],
                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                'error' => $_FILES['photos']['error'][$i],
                'size' => $_FILES['photos']['size'][$i],
            ];
            $filename = handleImageUpload('single_temp');

            if ($filename !== null) {
                $pdo->prepare(
                    "INSERT INTO photos_projets (projet_id, edition_id, url) VALUES (?, ?, ?)"
                )->execute([$projetId, $id, $filename]);
                $photosAjouteesCount++;
            }
        }
    }

    $pdo->commit();

    // --- Notifications aux donateurs, après le commit (échec email ≠ échec de la sauvegarde) ---
    if ($statut === 'validee') {
        $projetTitre = $pdo->prepare("SELECT titre FROM projets WHERE id = ?");
        $projetTitre->execute([$projetId]);
        $projetTitre = $projetTitre->fetchColumn();

        if (!$projetAvaitDejaUneEditionValidee) {
            // Première fois que ce projet devient visible/finançable : on prévient tous les anciens donateurs du site
            notifierNouveauProjetOuvert($pdo, $projetId, $projetTitre);
        } elseif ($ancienStatut !== 'validee' || $photosAjouteesCount > 0) {
            // Le projet avait déjà du contenu publié, et on vient d'y ajouter du nouveau (description/photos)
            notifierDonateursProjet($pdo, $projetId, $projetTitre);
        }
    }

    header('Location: projet_editions.php?projet_id=' . $projetId);
    exit;

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    gererErreur($e, "حدث خطأ أثناء العملية. يرجى المحاولة مرة أخرى أو التواصل مع الإدارة.");
}