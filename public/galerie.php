<?php
require_once '../config/db.php';
$pageTitle = 'معرض الصور';
include '../includes/header_public.php';

$photos = $pdo->query(
    "SELECT ph.url, p.titre FROM photos_projets ph JOIN projets p ON ph.projet_id = p.id ORDER BY ph.date_ajout DESC"
)->fetchAll();
?>

<section class="page-hero orange">
    <div class="container">
        <h1>معرض الصور</h1>
        <p>لحظات من أنشطة ومشاريع جمعية الجيل المبدع</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($photos)): ?>
            <p class="badge-empty">لا توجد صور لعرضها حاليا.</p>
        <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($photos as $ph): ?>
                <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>" alt="<?= htmlspecialchars($ph['titre']) ?>" loading="lazy">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>
