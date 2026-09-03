<?php

require_once 'config/db.php';
require_once 'includes/csrf.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: projets.php');
    exit;
}

verifierJetonCsrf();

if (!empty($_POST['website'])) {
    header('Location: projet_detail.php?id=' . (int)($_POST['projet_id'] ?? 0) . '#comments');
    exit;
}

// Rate limiting
if (isset($_SESSION['last_comment']) && (time() - $_SESSION['last_comment']) < 30) {
    header('Location: projet_detail.php?id=' . (int)($_POST['projet_id'] ?? 0) . '#comments');
    exit;
}
$_SESSION['last_comment'] = time();

$projetId = (int) ($_POST['projet_id'] ?? 0);
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($projetId > 0 && $nom !== '' && $message !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $nom = mb_substr($nom, 0, 150);
    $message = mb_substr($message, 0, 1000);

    $stmt = $pdo->prepare("SELECT id FROM projets WHERE id = ?");
    $stmt->execute([$projetId]);

    if ($stmt->fetch()) {
        // On vérifie si cet email a déjà commenté avant, sous quel nom
        $stmtNom = $pdo->prepare("SELECT nom FROM commentaires_projets WHERE email = ? ORDER BY date_commentaire ASC LIMIT 1");
        $stmtNom->execute([$email]);
        $ancienNom = $stmtNom->fetchColumn();

        // Si l'email est déjà connu, on impose son nom d'origine (empêche de changer d'identité)
        $nomFinal = $ancienNom ?: $nom;

        $pdo->prepare(
            "INSERT INTO commentaires_projets (projet_id, nom, email, message) VALUES (?, ?, ?, ?)"
        )->execute([$projetId, $nomFinal, $email, $message]);
    }
}

header('Location: projet_detail.php?id=' . $projetId . '#comments');
exit;