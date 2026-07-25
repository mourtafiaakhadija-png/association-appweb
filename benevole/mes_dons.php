<?php
session_start();
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';

$benevoleId = $_SESSION['benevole_id'];

// On récupère l'email du compte bénévole, et on l'utilise directement pour retrouver ses dons —
// pas besoin qu'il le retape, contrairement à la version publique de mes_dons.php
$stmtUser = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmtUser->execute([$benevoleId]);
$email = $stmtUser->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT d.*, p.titre, p.id as projet_id FROM dons d 
     JOIN projets p ON d.projet_id = p.id 
     WHERE d.email_donateur = ? ORDER BY d.date_don DESC"
);
$stmt->execute([$email]);
$dons = $stmt->fetchAll();

include '../includes/header_benevole.php';
?>

<h2>تبرعاتي</h2>
<p class="info-note">هذه تبرعاتكم المرتبطة ببريدكم الإلكتروني (<?= htmlspecialchars($email) ?>).</p>

<?php if (empty($dons)): ?>
    <p class="badge-empty">لم تقوموا بأي تبرع بعد بهذا البريد الإلكتروني.</p>
<?php else: ?>
    <p class="info-note">
        مجموع تبرعاتكم: <strong><?= number_format(array_sum(array_column($dons, 'montant')), 2) ?> درهم</strong>
        عبر <?= count($dons) ?> تبرع
    </p>
    <table class="rh-table">
        <thead>
            <tr><th>المشروع</th><th>المبلغ</th><th>طريقة الأداء</th><th>التاريخ</th></tr>
        </thead>
        <tbody>
        <?php foreach ($dons as $d): ?>
            <tr>
                <td><a href="../public/projet_detail.php?id=<?= $d['projet_id'] ?>" target="_blank"><?= htmlspecialchars($d['titre']) ?></a></td>
                <td><?= number_format($d['montant'], 2) ?> د.م.</td>
                <td><?= htmlspecialchars($d['mode_paiement']) ?></td>
                <td><?= htmlspecialchars($d['date_don']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer_benevole.php'; ?>