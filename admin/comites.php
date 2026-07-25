<?php
session_start();
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$editions = $pdo->query(
    "SELECT e.id AS edition_id, e.numero_edition, p.titre,
            (SELECT COUNT(*) FROM participations_comite WHERE edition_id = e.id AND statut = 'disponible') AS nb_disponibles,
            (SELECT COUNT(*) FROM participations_comite WHERE edition_id = e.id AND statut = 'confirme') AS nb_confirmes
     FROM projet_editions e
     JOIN projets p ON p.id = e.projet_id
     WHERE e.appel_benevoles_ouvert = 1
     ORDER BY e.date_creation DESC"
)->fetchAll();

include '../includes/header.php';
?>

<h2>إدارة اللجان</h2>
<p class="info-note">قائمة الإصدارات التي بها نداء مفتوح للتطوع.</p>

<table class="rh-table">
    <thead>
        <tr><th>المشروع</th><th>الإصدار</th><th>متوفرون</th><th>مؤكدون</th><th>الإجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($editions as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['titre']) ?></td>
            <td>#<?= $e['numero_edition'] ?></td>
            <td><?= $e['nb_disponibles'] ?></td>
            <td><?= $e['nb_confirmes'] ?></td>
            <td><a href="comite_membres.php?edition_id=<?= $e['edition_id'] ?>">إدارة الفريق</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($editions)): ?>
        <tr><td colspan="5">لا يوجد أي نداء تطوع مفتوح حاليا.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>