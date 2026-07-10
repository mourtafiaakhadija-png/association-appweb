<?php
require_once '../config/db.php';
$pageTitle = 'أعضاؤنا';
include '../includes/header_public.php';

$benevoles = $pdo->query("SELECT nom, prenom FROM users WHERE role = 'benevole' AND statut = 'actif' ORDER BY nom")->fetchAll();
$collaborateurs = $pdo->query("SELECT * FROM collaborateurs ORDER BY nom")->fetchAll();
$nbDonateurs = $pdo->query("SELECT COUNT(DISTINCT email_donateur) FROM dons WHERE email_donateur IS NOT NULL")->fetchColumn();
?>

<section class="page-hero">
    <div class="container">
        <h1>أعضاؤنا</h1>
        <p>المتطوعون والداعمون الذين يصنعون الفرق كل يوم إلى جانبنا</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">فريقنا</span>
            <h2>المتطوعون</h2>
        </div>
        <?php if (empty($benevoles)): ?>
            <p class="badge-empty">انضموا إلينا وكونوا أول متطوع يظهر هنا! <a href="join_us.php" style="color:var(--orange); font-weight:700;">انضم إلينا</a></p>
        <?php else: ?>
        <div class="team-grid">
            <?php foreach ($benevoles as $b): ?>
                <div class="team-card">
                    <div class="team-body" style="text-align:center; padding-top:1.5rem;">
                        <div class="comment-avatar" style="margin:0 auto 0.8rem; width:56px; height:56px; font-size:1.3rem;">
                            <?= htmlspecialchars(mb_substr($b['prenom'], 0, 1)) ?>
                        </div>
                        <h3><?= htmlspecialchars($b['prenom'] . ' ' . $b['nom']) ?></h3>
                        <div class="team-role">متطوع(ة)</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">بدعمكم</span>
            <h2>شكرا لداعمينا</h2>
            <p><?= (int) $nbDonateurs ?> متبرع ساهموا في مشاريعنا حتى الآن</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">شراكة</span>
            <h2>شركاؤنا</h2>
        </div>
        <?php if (empty($collaborateurs)): ?>
            <p class="badge-empty">سيتم عرض شركائنا هنا قريبا.</p>
        <?php else: ?>
        <div class="team-grid">
            <?php foreach ($collaborateurs as $c): ?>
                <div class="team-card">
                    <?php if ($c['logo']): ?>
                        <img src="../uploads/<?= htmlspecialchars($c['logo']) ?>" style="object-fit:contain; padding:1.5rem; background:#fff;">
                    <?php endif; ?>
                    <div class="team-body">
                        <h3><?= htmlspecialchars($c['nom']) ?></h3>
                        <p><?= htmlspecialchars($c['description'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>
