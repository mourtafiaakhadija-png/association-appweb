<?php
session_start();
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';

$benevoleId = $_SESSION['benevole_id'];

$stmt = $pdo->prepare(
    "SELECT e.id AS edition_id, e.numero_edition, e.commentaire_admin, p.titre AS projet_titre, 'edition' AS type
     FROM projet_editions e JOIN projets p ON p.id = e.projet_id
     WHERE p.responsable_id = ? AND e.statut = 'a_corriger'"
);
$stmt->execute([$benevoleId]);
$editionsACorriger = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT e.id AS edition_id, e.numero_edition, e.commentaire_rapport AS commentaire_admin, p.titre AS projet_titre, 'rapport' AS type
     FROM projet_editions e JOIN projets p ON p.id = e.projet_id
     WHERE p.responsable_id = ? AND e.rapport_statut = 'a_corriger'"
);
$stmt->execute([$benevoleId]);
$rapportsACorriger = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT e.id AS edition_id, e.numero_edition, m.commentaire_admin, p.titre AS projet_titre, 'maj' AS type
     FROM mises_a_jour_edition m
     JOIN projet_editions e ON e.id = m.edition_id
     JOIN projets p ON p.id = e.projet_id
     WHERE p.responsable_id = ? AND m.statut = 'a_corriger'"
);
$stmt->execute([$benevoleId]);
$majACorriger = $stmt->fetchAll();

$tousLesItems = array_merge($editionsACorriger, $rapportsACorriger, $majACorriger);

$typeLabels = [
    'edition' => ['icon' => '📝', 'texte' => 'الإصدار بحاجة إلى تصحيح'],
    'rapport' => ['icon' => '📄', 'texte' => 'الرابور بحاجة إلى تصحيح'],
    'maj'     => ['icon' => '🔄', 'texte' => 'التحديث بحاجة إلى تصحيح'],
];

include '../includes/header_benevole.php';
?>

<h2>🔔 الإشعارات</h2>
<p class="info-note">هذه العناصر بحاجة إلى تدخلكم — الإدارة طلبت تصحيحا عليها.</p>

<?php if (empty($tousLesItems)): ?>
    <p class="badge-empty">لا توجد أي إشعارات حاليا. </p>
<?php else: ?>
    <div class="notif-list">
        <?php foreach ($tousLesItems as $item): ?>
            <a href="projet_edition_form.php?id=<?= $item['edition_id'] ?>" class="notif-item">
                <span class="notif-item-icon"><?= $typeLabels[$item['type']]['icon'] ?></span>
                <div class="notif-item-body">
                    <strong><?= htmlspecialchars($item['projet_titre']) ?> — إصدار #<?= $item['numero_edition'] ?></strong>
                    <p><?= $typeLabels[$item['type']]['texte'] ?></p>
                    <?php if (!empty($item['commentaire_admin'])): ?>
                        <div class="rapport-comment"><?= htmlspecialchars($item['commentaire_admin']) ?></div>
                    <?php endif; ?>
                </div>
                <span class="notif-item-arrow">←</span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer_benevole.php'; ?>