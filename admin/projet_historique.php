<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$projetId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM projets WHERE id = ?");
$stmt->execute([$projetId]);
$projet = $stmt->fetch();
if (!$projet) die("Projet introuvable.");

// Ajout manuel d'une note d'historique (ex: "Visite de terrain effectuée", "Réception d'un don en nature")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['note'])) {
    $pdo->prepare(
        "INSERT INTO historique_projets (projet_id, description_action, auteur_id) VALUES (?, ?, ?)"
    )->execute([$projetId, trim($_POST['note']), $_SESSION['user_id']]);
    header('Location: projet_historique.php?id=' . $projetId);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT h.*, u.nom, u.prenom 
     FROM historique_projets h 
     LEFT JOIN users u ON h.auteur_id = u.id 
     WHERE h.projet_id = ? ORDER BY h.date_action DESC"
);
$stmt->execute([$projetId]);
$historique = $stmt->fetchAll();

include '../includes/header.php';
?>

<h2>Historique / Rapport — <?= htmlspecialchars($projet['titre']) ?></h2>
<p><a href="projets.php">&larr; Retour aux projets</a></p>

<form method="POST" class="historique-form">
    <label>Ajouter une note manuelle à l'historique</label>
    <textarea name="note" rows="2" placeholder="Ex: Visite de terrain effectuée le 12/07, don en nature reçu..." required></textarea>
    <button type="submit">Ajouter à l'historique</button>
</form>

<div class="timeline">
    <?php foreach ($historique as $h): ?>
        <div class="timeline-item">
            <div class="timeline-date"><?= htmlspecialchars($h['date_action']) ?></div>
            <div class="timeline-content">
                <p><?= htmlspecialchars($h['description_action']) ?></p>
                <span class="timeline-author">
                    Par : <?= $h['nom'] ? htmlspecialchars($h['prenom'] . ' ' . $h['nom']) : 'Système' ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($historique)): ?>
        <p class="info-note">Aucun historique pour l'instant.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
