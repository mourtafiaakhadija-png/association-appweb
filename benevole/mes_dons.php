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
    "SELECT d.*, p.titre, p.id as projet_id, e.numero_edition, e.budget_collecte, e.budget_prevu
     FROM dons d 
     JOIN projets p ON d.projet_id = p.id 
     LEFT JOIN projet_editions e ON d.edition_id = e.id
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
            <tr><th>المشروع</th><th>المبلغ</th><th>طريقة الأداء</th><th>التاريخ</th><th>تقدم هذا الإصدار</th></tr>
        </thead>
        <tbody>
        <?php foreach ($dons as $d): ?>
            <tr>
                <td>
                    <?php if ($d['numero_edition']): ?>
                        <a href="../public/projet_detail.php?id=<?= $d['projet_id'] ?>#edition-<?= $d['numero_edition'] ?>" target="_blank">
                            <?= htmlspecialchars($d['titre']) ?> — إصدار #<?= $d['numero_edition'] ?>
                        </a>
                    <?php else: ?>
                        <a href="../public/projet_detail.php?id=<?= $d['projet_id'] ?>" target="_blank"><?= htmlspecialchars($d['titre']) ?></a>
                    <?php endif; ?>
                </td>
                <td><?= number_format($d['montant'], 2) ?> د.م.</td>
                <td><?= htmlspecialchars($d['mode_paiement']) ?></td>
                <td><?= htmlspecialchars($d['date_don']) ?></td>
                <td>
                    <?php if ($d['numero_edition'] && $d['budget_prevu'] > 0):
                        $pctDon = min(100, round(($d['budget_collecte'] / $d['budget_prevu']) * 100));
                    ?>
                        <div class="project-progress" style="width:100px;"><div class="project-progress-fill" style="width:<?= $pctDon ?>%;"></div></div>
                        <small><?= $pctDon ?>%</small>
                    <?php else: ?>
                        <span style="color:#999;">-</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer_benevole.php'; ?>