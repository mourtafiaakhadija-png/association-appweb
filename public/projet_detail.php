<?php
require_once '../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT p.*, c.nom AS categorie_nom FROM projets p 
     LEFT JOIN categories_projets c ON p.categorie_id = c.id WHERE p.id = ?"
);
$stmt->execute([$id]);
$projet = $stmt->fetch();

if (!$projet) {
    require_once '../includes/header_public.php';
    echo '<section class="section"><div class="container"><p class="badge-empty">المشروع غير موجود.</p></div></section>';
    include '../includes/footer_public.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM photos_projets WHERE projet_id = ? ORDER BY date_ajout");
$stmt->execute([$id]);
$photos = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM commentaires_projets WHERE projet_id = ? ORDER BY date_commentaire DESC");
$stmt->execute([$id]);
$commentaires = $stmt->fetchAll();

$cibleLabels = ['famille' => 'عائلة', 'village' => 'قرية', 'ecole' => 'مدرسة', 'orphelin' => 'اليتيم'];
$statutLabels = ['en_cours' => 'قيد التنفيذ', 'termine' => 'مكتمل', 'suspendu' => 'متوقف مؤقتا'];

$pageTitle = $projet['titre'];
include '../includes/header_public.php';

$pct = $projet['budget_prevu'] > 0 ? min(100, round(($projet['budget_collecte'] / $projet['budget_prevu']) * 100)) : 0;
?>

<section class="page-hero orange">
    <div class="container">
        <h1><?= htmlspecialchars($projet['titre']) ?></h1>
        <p><?= htmlspecialchars($projet['categorie_nom'] ?? '') ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="breadcrumb"><a href="projets.php">مشاريعنا</a> ← <?= htmlspecialchars($projet['titre']) ?></p>

        <div class="projet-detail-hero">
            <div>
                <?php if (!empty($photos)): ?>
                    <img src="../uploads/<?= htmlspecialchars($photos[0]['url']) ?>" class="projet-gallery-main" id="projetMainImage">
                    <?php if (count($photos) > 1): ?>
                    <div class="projet-gallery-thumbs">
                        <?php foreach ($photos as $ph): ?>
                            <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="project-card-img placeholder" style="height:340px; border-radius:14px;">لا توجد صورة</div>
                <?php endif; ?>
            </div>

            <div class="projet-info-card">
                <span class="project-tag"><?= htmlspecialchars($statutLabels[$projet['statut']] ?? $projet['statut']) ?></span>

                <div class="budget-figures">
                    <span><?= number_format($projet['budget_collecte'], 0) ?> د.م.</span>
                    <span style="color:var(--ink-soft); font-weight:600;">من <?= number_format($projet['budget_prevu'], 0) ?> د.م.</span>
                </div>
                <div class="project-progress"><div class="project-progress-fill" style="width:<?= $pct ?>%;"></div></div>
                <div class="project-progress-label"><?= $pct ?>% تم جمعه</div>

                <ul class="projet-meta-list">
                    <li><span>الفئة المستهدفة</span><span><?= htmlspecialchars($cibleLabels[$projet['cible_type']] ?? $projet['cible_type']) ?></span></li>
                    <?php if ($projet['cible_details']): ?>
                    <li><span>التفاصيل</span><span><?= htmlspecialchars($projet['cible_details']) ?></span></li>
                    <?php endif; ?>
                    <?php if ($projet['date_debut']): ?>
                    <li><span>تاريخ الانطلاق</span><span><?= htmlspecialchars($projet['date_debut']) ?></span></li>
                    <?php endif; ?>
                </ul>

                <a href="don.php?projet_id=<?= $projet['id'] ?>" class="btn-donate-full">تبرع لهذا المشروع 🤲</a>
            </div>
        </div>

        <div class="projet-description">
            <h2>عن المشروع</h2>
            <p style="font-size:1.05rem; white-space:pre-line;"><?= htmlspecialchars($projet['description']) ?></p>
        </div>

        <!-- ================= COMMENTAIRES ================= -->
        <div class="comments-section">
            <h2>التعليقات (<?= count($commentaires) ?>)</h2>

            <form method="POST" action="comment_action.php" class="comment-form">
                <input type="hidden" name="projet_id" value="<?= $projet['id'] ?>">
                <input type="text" name="nom" placeholder="اسمك" required maxlength="150">
                <textarea name="message" placeholder="اكتب تعليقك هنا..." rows="3" required maxlength="1000"></textarea>
                <button type="submit">نشر التعليق</button>
            </form>

            <div class="comments-list">
                <?php foreach ($commentaires as $c): ?>
                    <div class="comment-item">
                        <div class="comment-avatar"><?= htmlspecialchars(mb_substr($c['nom'], 0, 1)) ?></div>
                        <div class="comment-body">
                            <div class="comment-header">
                                <strong><?= htmlspecialchars($c['nom']) ?></strong>
                                <span class="comment-date"><?= htmlspecialchars($c['date_commentaire']) ?></span>
                            </div>
                            <p><?= nl2br(htmlspecialchars($c['message'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($commentaires)): ?>
                    <p class="badge-empty">لا توجد تعليقات بعد. كن أول من يعلق!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>
