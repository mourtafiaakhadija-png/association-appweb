<?php
require_once '../config/db.php';
$pageTitle = 'تبرعاتي';

$email = trim($_GET['email'] ?? '');
$dons = [];
$searched = false;

if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $searched = true;
    $stmt = $pdo->prepare(
        "SELECT d.*, p.titre, p.id as projet_id FROM dons d 
         JOIN projets p ON d.projet_id = p.id 
         WHERE d.email_donateur = ? ORDER BY d.date_don DESC"
    );
    $stmt->execute([$email]);
    $dons = $stmt->fetchAll();
}

include '../includes/header_public.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>تبرعاتي</h1>
        <p>أدخلوا بريدكم الإلكتروني لعرض سجل تبرعاتكم</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:700px;">
        <form method="GET" class="search-dons-form">
            <input type="email" name="email" placeholder="بريدكم الإلكتروني" value="<?= htmlspecialchars($email) ?>" required>
            <button type="submit">بحث</button>
        </form>

        <?php if ($searched): ?>
            <?php if (empty($dons)): ?>
                <p class="badge-empty">لا توجد تبرعات مرتبطة بهذا البريد الإلكتروني.</p>
            <?php else: ?>
                <p style="text-align:center; color:var(--ink-soft); margin:1.5rem 0;">
                    مجموع تبرعاتكم: <strong style="color:var(--blue-dark);"><?= number_format(array_sum(array_column($dons, 'montant')), 2) ?> درهم</strong>
                    عبر <?= count($dons) ?> تبرع
                </p>
                <div class="dons-list">
                    <?php foreach ($dons as $d): ?>
                        <div class="don-item">
                            <div>
                                <strong><?= htmlspecialchars($d['titre']) ?></strong>
                                <div class="don-item-date"><?= htmlspecialchars($d['date_don']) ?> — <?= htmlspecialchars($d['mode_paiement']) ?></div>
                            </div>
                            <div class="don-item-amount"><?= number_format($d['montant'], 2) ?> د.م.</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>
