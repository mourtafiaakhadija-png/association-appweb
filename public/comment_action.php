<?php
// public/comment_action.php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: projets.php');
    exit;
}

$projetId = (int) ($_POST['projet_id'] ?? 0);
$nom = trim($_POST['nom'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation simple côté serveur (ne jamais se fier uniquement au HTML "required")
if ($projetId > 0 && $nom !== '' && $message !== '') {
    $nom = mb_substr($nom, 0, 150);
    $message = mb_substr($message, 0, 1000);

    $stmt = $pdo->prepare("SELECT id FROM projets WHERE id = ?");
    $stmt->execute([$projetId]);
    if ($stmt->fetch()) {
        $pdo->prepare(
            "INSERT INTO commentaires_projets (projet_id, nom, message) VALUES (?, ?, ?)"
        )->execute([$projetId, $nom, $message]);
    }
}

header('Location: projet_detail.php?id=' . $projetId . '#comments');
exit;
