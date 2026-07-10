<?php
require_once '../config/db.php';
$pageTitle = 'المكتب';
include '../includes/header_public.php';

$bureau = $pdo->query(
    "SELECT bm.fonction, bm.photo, bm.bio, u.nom, u.prenom
     FROM bureau_membres bm JOIN users u ON bm.user_id = u.id
     ORDER BY u.nom"
)->fetchAll();
?>

<section class="page-hero">
    <div class="container">
        <h1>أعضاء المكتب</h1>
        <p>الفريق المسؤول عن قيادة جمعية الجيل المبدع وتنسيق مشاريعها</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($bureau)): ?>
            <p class="badge-empty">سيتم عرض أعضاء المكتب هنا قريبا.</p>
        <?php else: ?>
        <div class="team-grid">
            <?php foreach ($bureau as $m): ?>
                <div class="team-card">
                    <?php if ($m['photo']): ?>
                        <img src="../uploads/<?= htmlspecialchars($m['photo']) ?>">
                    <?php else: ?>
                        <div class="project-card-img placeholder" style="aspect-ratio:1;">📷</div>
                    <?php endif; ?>
                    <div class="team-body">
                        <h3><?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></h3>
                        <div class="team-role"><?= htmlspecialchars($m['fonction']) ?></div>
                        <?php if ($m['bio']): ?><p><?= htmlspecialchars($m['bio']) ?></p><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>
