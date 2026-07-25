<?php
require_once '../config/db.php';
$pageTitle = 'شكرا لكم';

$donId = (int) ($_GET['don_id'] ?? 0);
$don = null;
if ($donId > 0) {
    $stmt = $pdo->prepare(
        "SELECT d.*, p.titre FROM dons d JOIN projets p ON d.projet_id = p.id WHERE d.id = ?"
    );
    $stmt->execute([$donId]);
    $don = $stmt->fetch();
}

include '../includes/header_public.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>شكرا لكم 🌙</h1>
        <p>تم تسجيل نيتكم للتبرع بنجاح</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:560px;">
        <div class="don-info-card" style="text-align:center;">
            <?php if ($don): ?>
                <p style="font-size:1.1rem;">تبرعكم بمبلغ <strong><?= number_format($don['montant'], 2) ?> درهم</strong> لمشروع <strong><?= htmlspecialchars($don['titre']) ?></strong> تم تسجيله بنجاح.</p>
                <p>أرسلنا لكم رسالة تأكيد بالبريد الإلكتروني تحتوي على معلومات الحساب البنكي لإتمام التحويل.</p>
            <?php else: ?>
                <p>شكرا على نيتكم الطيبة في التبرع.</p>
            <?php endif; ?>

            <div class="rib-box" style="margin-top:1.5rem;">
                <span>RIB</span>
                <strong>350810000000110741288</strong>
            </div>

            <a href="projets.php" class="btn-outline-blue" style="margin-top:1.5rem;">اكتشف مشاريع أخرى</a>
        </div>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>
