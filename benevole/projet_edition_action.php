<?php
session_start();
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';
require_once '../includes/upload_helper.php';

$benevoleId = $_SESSION['benevole_id'];

// --- Suppression d'une photo ---
if (isset($_GET['delete_photo'])) {
    $photoId = (int) $_GET['delete_photo'];
    $editionId = (int) $_GET['edition_id'];

    // Vérifie que cette photo appartient bien à une édition d'un projet dont ce bénévole est responsable
    $check = $pdo->prepare(
        "SELECT ph.id FROM photos_projets ph
         JOIN projet_editions e ON e.id = ph.edition_id
         JOIN projets p ON p.id = e.projet_id
         WHERE ph.id = ? AND p.responsable_id = ?"
    );
    $check->execute([$photoId, $benevoleId]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM photos_projets WHERE id = ?")->execute([$photoId]);
    }
    header('Location: projet_edition_form.php?id=' . $editionId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$isEdit = $id !== null;
$projetId = (int) $_POST['projet_id'];

// Vérification de sécurité : ce projet doit appartenir à ce bénévole
$stmtProjet = $pdo->prepare("SELECT id FROM projets WHERE id = ? AND responsable_id = ?");
$stmtProjet->execute([$projetId, $benevoleId]);
if (!$stmtProjet->fetch()) {
    die("غير مسموح لك بالوصول إلى هذا المشروع.");
}

// Si édition existante : vérifier qu'elle appartient bien à ce projet et qu'elle est modifiable
$modifiable = true;
if ($isEdit) {
    $stmtEdition = $pdo->prepare("SELECT statut FROM projet_editions WHERE id = ? AND projet_id = ?");
    $stmtEdition->execute([$id, $projetId]);
    $edition = $stmtEdition->fetch();
    if (!$edition) die("الإصدار غير موجود.");
    $modifiable = in_array($edition['statut'], ['brouillon', 'a_corriger']);
}

try {
    $pdo->beginTransaction();

    if ($modifiable) {
        $numeroEdition = (int) $_POST['numero_edition'];
        $description = trim($_POST['description']);
        $budgetPrevu = (float) $_POST['budget_prevu'];
        $dateDebut = $_POST['date_debut'] ?: null;
        $dateFin = $_POST['date_fin'] ?: null;

        if ($isEdit) {
            // Repasse en "brouillon" si elle avait été renvoyée pour correction : le bénévole doit
            // la resoumettre explicitement via "إرسال للمصادقة" une fois ses modifications faites
            $pdo->prepare(
                "UPDATE projet_editions SET numero_edition=?, description=?, budget_prevu=?, 
                 date_debut=?, date_fin=?, statut='brouillon', commentaire_admin=NULL WHERE id=?"
            )->execute([$numeroEdition, $description, $budgetPrevu, $dateDebut, $dateFin, $id]);
        } else {
            $pdo->prepare(
                "INSERT INTO projet_editions 
                 (projet_id, numero_edition, description, budget_prevu, date_debut, date_fin, statut, cree_par) 
                 VALUES (?, ?, ?, ?, ?, ?, 'brouillon', ?)"
            )->execute([$projetId, $numeroEdition, $description, $budgetPrevu, $dateDebut, $dateFin, $benevoleId]);
            $id = $pdo->lastInsertId();
        }

        // Upload des photos multiples
        if (!empty($_FILES['photos']['name'][0])) {
            $count = count($_FILES['photos']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $_FILES['single_temp'] = [
                    'name' => $_FILES['photos']['name'][$i], 'type' => $_FILES['photos']['type'][$i],
                    'tmp_name' => $_FILES['photos']['tmp_name'][$i], 'error' => $_FILES['photos']['error'][$i],
                    'size' => $_FILES['photos']['size'][$i],
                ];
                $filename = handleImageUpload('single_temp');
                if ($filename !== null) {
                    $pdo->prepare("INSERT INTO photos_projets (projet_id, edition_id, url) VALUES (?, ?, ?)")->execute([$projetId, $id, $filename]);
                }
            }
        }
    }

    // Upload du rapport : autorisé MÊME si l'édition n'est plus modifiable (déjà validée)
    $fichierRapport = handleDocumentUpload('rapport');
    if ($fichierRapport !== null) {
        $pdo->prepare("UPDATE projet_editions SET fichier_rapport = ? WHERE id = ?")->execute([$fichierRapport, $id]);
    }

    $pdo->commit();
    header('Location: projet_editions.php?projet_id=' . $projetId);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("خطأ: " . htmlspecialchars($e->getMessage()));
}