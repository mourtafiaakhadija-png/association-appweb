<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../includes/upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: rh.php');
    exit;
}

verifierJetonCsrf();
$type = $_POST['type'] ?? 'bureau';
$id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$isEdit = $id !== null;
// --- Activer / désactiver un compte bénévole ---
if (isset($_POST['toggle_statut_benevole'])) {
    $userId = (int) $_POST['toggle_statut_benevole'];
    $stmt = $pdo->prepare("SELECT statut FROM users WHERE id = ? AND role = 'benevole'");
    $stmt->execute([$userId]);
    $statutActuel = $stmt->fetchColumn();

    if ($statutActuel !== false) {
        $nouveauStatut = ($statutActuel === 'actif') ? 'inactif' : 'actif';
        $pdo->prepare("UPDATE users SET statut = ? WHERE id = ?")->execute([$nouveauStatut, $userId]);
    }
    header('Location: rh.php?tab=benevoles');
    exit;
}

try {
    if ($type === 'bureau') {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $fonction = trim($_POST['fonction']);
        $bio = trim($_POST['bio'] ?? '');
        $photo = handleImageUpload('photo');

        $pdo->beginTransaction();

        if ($isEdit) {
            $userId = (int) $_POST['user_id'];

            $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ? WHERE id = ?")
                ->execute([$nom, $prenom, $email, $userId]);

            if ($photo !== null) {
                $pdo->prepare("UPDATE bureau_membres SET fonction = ?, bio = ?, photo = ? WHERE id = ?")
                    ->execute([$fonction, $bio, $photo, $id]);
            } else {
                $pdo->prepare("UPDATE bureau_membres SET fonction = ?, bio = ? WHERE id = ?")
                    ->execute([$fonction, $bio, $id]);
            }
        } else {
            // Mot de passe temporaire généré aléatoirement (le membre pourra le changer plus tard)
            $tempPassword = password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT);

            $pdo->prepare(
                "INSERT INTO users (nom, prenom, email, password, role, statut) VALUES (?, ?, ?, ?, 'bureau', 'actif')"
            )->execute([$nom, $prenom, $email, $tempPassword]);
            $userId = $pdo->lastInsertId();

            $pdo->prepare(
                "INSERT INTO bureau_membres (user_id, fonction, photo, bio) VALUES (?, ?, ?, ?)"
            )->execute([$userId, $fonction, $photo, $bio]);
        }

        $pdo->commit();
        header('Location: rh.php?tab=bureau');
        exit;

    } elseif ($type === 'collaborateur') {
        $nom = trim($_POST['nom']);
        $description = trim($_POST['description'] ?? '');
        $logo = handleImageUpload('logo');

        if ($isEdit) {
            if ($logo !== null) {
                $pdo->prepare("UPDATE collaborateurs SET nom = ?, description = ?, logo = ? WHERE id = ?")
                    ->execute([$nom, $description, $logo, $id]);
            } else {
                $pdo->prepare("UPDATE collaborateurs SET nom = ?, description = ? WHERE id = ?")
                    ->execute([$nom, $description, $id]);
            }
        } else {
            $pdo->prepare("INSERT INTO collaborateurs (nom, description, logo) VALUES (?, ?, ?)")
                ->execute([$nom, $description, $logo]);
        }

        header('Location: rh.php?tab=collaborateurs');
        exit;
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    gererErreur($e, "حدث خطأ أثناء العملية. يرجى المحاولة مرة أخرى أو التواصل مع الإدارة.");
}