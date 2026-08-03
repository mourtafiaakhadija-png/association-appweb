<?php
session_start();
require_once '../includes/auth_check_benevole.php';
require_once '../includes/i18n_admin.php';
require_once '../config/db.php';

$benevoleId = $_SESSION['benevole_id'];

// Les projets dont CE bénévole est responsable, avec les stats de leur édition la plus récente
// (peu importe son statut : le bénévole doit voir ses brouillons/en attente, pas juste le validé)
$stmt = $pdo->prepare(
    "SELECT p.id, p.titre, p.statut AS projet_statut,
            e.id AS edition_id, e.numero_edition, e.statut AS edition_statut,
            e.budget_prevu, e.budget_collecte,
            (SELECT COUNT(*) FROM dons WHERE edition_id = e.id) AS nb_dons
     FROM projets p
     LEFT JOIN projet_editions e ON e.id = (
         SELECT id FROM projet_editions
         WHERE projet_id = p.id
         ORDER BY numero_edition DESC, date_debut DESC
         LIMIT 1
     )
     WHERE p.responsable_id = ?
     ORDER BY p.titre"
);
$stmt->execute([$benevoleId]);
$projets = $stmt->fetchAll();
// Projets où ce bénévole est membre CONFIRMÉ du comité (mais pas forcément responsable)
$stmt = $pdo->prepare(
    "SELECT DISTINCT p.id, p.titre, e.numero_edition, e.statut AS edition_statut
     FROM participations_comite pc
     JOIN projet_editions e ON e.id = pc.edition_id
     JOIN projets p ON p.id = e.projet_id
     WHERE pc.user_id = ? AND pc.statut = 'confirme' AND p.responsable_id != ?
     ORDER BY p.titre"
);
$stmt->execute([$benevoleId, $benevoleId]);
$projetsComite = $stmt->fetchAll();

include '../includes/header_benevole.php';
?>

<h2>مرحبا <?= htmlspecialchars($_SESSION['benevole_prenom']) ?> <i class="fa-solid fa-hand-peace"></i></h2>
<p class="info-note">هذه هي المشاريع التي أنت مسؤول عنها. اضغط على أي مشروع للاطلاع على تفاصيله وإدارة إصداراته.</p>

<div class="benevole-projets-grid">
    <?php foreach ($projets as $p): ?>
        <div class="benevole-projet-card">
            <h3><?= htmlspecialchars($p['titre']) ?></h3>

            <?php if ($p['edition_id']): ?>
                <span class="badge badge-<?= $p['edition_statut'] ?>"><?= label('statut_edition', $p['edition_statut']) ?></span>
                <span class="badge-secondary">الإصدار #<?= $p['numero_edition'] ?></span>

                <?php $pct = $p['budget_prevu'] > 0 ? min(100, round(($p['budget_collecte'] / $p['budget_prevu']) * 100)) : 0; ?>
                <div class="project-progress"><div class="project-progress-fill" style="width:<?= $pct ?>%;"></div></div>
                <p class="projet-budget"><?= number_format($p['budget_collecte'], 0) ?> / <?= number_format($p['budget_prevu'], 0) ?> د.م. (<?= $pct ?>%)</p>
                <p class="benevole-stat"><i class="fa-solid fa-coins"></i> عدد التبرعات المستلمة: <strong><?= $p['nb_dons'] ?></strong></p>
            <?php else: ?>
                <p class="badge-empty">لا يوجد أي إصدار لهذا المشروع بعد.</p>
            <?php endif; ?>

            <a href="projet_editions.php?projet_id=<?= $p['id'] ?>" class="btn-add"><i class="fa-solid fa-folder-open"></i> إدارة الإصدارات</a>
        </div>
    <?php endforeach; ?>

    <?php if (empty($projets)): ?>
        <p class="info-note">لست مسؤولا عن أي مشروع حاليا. تواصل مع الإدارة إذا كان هذا خطأ.</p>
    <?php endif; ?>
</div>
<?php if (!empty($projetsComite)): ?>
<h2 style="margin-top:2.5rem;">المشاريع التي أنا عضو مؤكد في لجنتها</h2>
<div class="benevole-projets-grid">
    <?php foreach ($projetsComite as $pc): ?>
        <div class="benevole-projet-card">
            <h3><?= htmlspecialchars($pc['titre']) ?></h3>
            <span class="badge badge-<?= $pc['edition_statut'] ?>">الإصدار #<?= $pc['numero_edition'] ?></span>
            <a href="../public/projet_detail.php?id=<?= $pc['id'] ?>" target="_blank" class="btn-add">👁 عرض المشروع</a>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include '../includes/footer_benevole.php'; ?>