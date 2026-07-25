<?php
session_start();
require_once '../includes/i18n_admin.php';
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

<h2>السجل / التقرير — <?= htmlspecialchars($projet['titre']) ?></h2>
<p><a href="projets.php">&larr;الرجوع للمشاريع</a></p>

<form method="POST" class="historique-form">
    <label>إضافة ملاحظة يدوية إلى السجل</label>
    <textarea name="note" rows="2" placeholder="مثال: زيارة ميدانية أُجريت بتاريخ 12/07، استلام تبرع عيني ..." required></textarea>
    <button type="submit">إضافة إلى السجل</button>
</form>

<div class="timeline">
    <?php foreach ($historique as $h): ?>
        <div class="timeline-item">
            <div class="timeline-date"><?= htmlspecialchars($h['date_action']) ?></div>
            <div class="timeline-content">
                <p><?= htmlspecialchars($h['description_action']) ?></p>
                <span class="timeline-author">
                    بواسطة :<?= $h['nom'] ? htmlspecialchars($h['prenom'] . ' ' . $h['nom']) : 'المسؤول' ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($historique)): ?>
        <p class="info-note">Aucun historique pour l'instant.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
