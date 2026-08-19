<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';
require_once '../includes/upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

verifierJetonCsrf();

$benevoleId = $_SESSION['benevole_id'];
// --- Suppression d'une mise à jour non publiée ---
if (isset($_POST['delete_maj_id'])) {
    $majId = (int) $_POST['delete_maj_id'];
    $stmtV = $pdo->prepare(
        "SELECT m.id FROM mises_a_jour_edition m
         JOIN projet_editions e ON e.id = m.edition_id
         JOIN projets p ON p.id = e.projet_id
         WHERE m.id = ? AND p.responsable_id = ? AND m.statut IN ('en_attente', 'a_corriger')"
    );
    $stmtV->execute([$majId, $benevoleId]);
    if ($stmtV->fetch()) {
        $pdo->prepare("DELETE FROM mises_a_jour_edition WHERE id = ?")->execute([$majId]);
    }
    header('Location: projet_edition_form.php?id=' . $_POST['edition_id']);
    exit;
}

// --- Modification d'une mise à jour non publiée (correction demandée par l'admin) ---
if (isset($_POST['update_maj_id'])) {
    $majId = (int) $_POST['update_maj_id'];
    $nouveauContenu = trim($_POST['contenu']);

    $stmtV = $pdo->prepare(
        "SELECT m.id FROM mises_a_jour_edition m
         JOIN projet_editions e ON e.id = m.edition_id
         JOIN projets p ON p.id = e.projet_id
         WHERE m.id = ? AND p.responsable_id = ? AND m.statut IN ('en_attente', 'a_corriger')"
    );
    $stmtV->execute([$majId, $benevoleId]);
    if (!$stmtV->fetch()) {
        die("غير مسموح لك بتعديل هذا التحديث.");
    }

    // On repasse en "en_attente" : la correction doit être revue par l'admin
    $pdo->prepare(
        "UPDATE mises_a_jour_edition SET contenu = ?, statut = 'en_attente', commentaire_admin = NULL WHERE id = ?"
    )->execute([$nouveauContenu, $majId]);

    // Photos supplémentaires éventuelles, rattachées à cette même mise à jour
    if (!empty($_FILES['photos']['name'][0])) {
        $stmtProjet = $pdo->prepare("SELECT projet_id FROM mises_a_jour_edition m JOIN projet_editions e ON e.id = m.edition_id WHERE m.id = ?");
        $stmtProjet->execute([$majId]);
        $projetIdMaj = $stmtProjet->fetchColumn();

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
                $pdo->prepare("INSERT INTO photos_projets (projet_id, edition_id, maj_id, url) VALUES (?, ?, ?, ?)")
                    ->execute([$projetIdMaj, $_POST['edition_id'], $majId, $filename]);
            }
        }
    }

    header('Location: projet_edition_form.php?id=' . $_POST['edition_id']);
    exit;
}
$editionId = (int) $_POST['edition_id'];
$contenu = trim($_POST['contenu']);

if ($contenu === '') {
    die("لا يمكن إرسال تحديث فارغ.");
}

// Vérification de sécurité : cette édition appartient bien à un projet dont ce bénévole est responsable
$stmt = $pdo->prepare(
    "SELECT e.id FROM projet_editions e
     JOIN projets p ON p.id = e.projet_id
     WHERE e.id = ? AND p.responsable_id = ?"
);
$stmt->execute([$editionId, $benevoleId]);
if (!$stmt->fetch()) {
    die("غير مسموح لك بالوصول إلى هذا الإصدار.");
}

try {
    $pdo->beginTransaction();

    $pdo->prepare(
        "INSERT INTO mises_a_jour_edition (edition_id, contenu, statut, auteur_id) VALUES (?, ?, 'en_attente', ?)"
    )->execute([$editionId, $contenu, $benevoleId]);
    $majId = $pdo->lastInsertId();

    $stmtProjet = $pdo->prepare("SELECT projet_id FROM projet_editions WHERE id = ?");
    $stmtProjet->execute([$editionId]);
    $projetId = $stmtProjet->fetchColumn();

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
                    "INSERT INTO photos_projets (projet_id, edition_id, maj_id, url) VALUES (?, ?, ?, ?)"
                )->execute([$projetId, $editionId, $majId, $filename]);
            }
        }
    }

    $pdo->commit();
    header('Location: projet_edition_form.php?id=' . $editionId);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("حدث خطأ أثناء إرسال التحديث.");
}