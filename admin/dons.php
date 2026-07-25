<?php
session_start();
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$filtreProjet = $_GET['projet_id'] ?? '';

$sql = "SELECT d.*, p.titre FROM dons d JOIN projets p ON d.projet_id = p.id WHERE 1=1";
$params = [];
if ($filtreProjet !== '') {
    $sql .= " AND d.projet_id = ?";
    $params[] = $filtreProjet;
}
$sql .= " ORDER BY d.date_don DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dons = $stmt->fetchAll();

$totalGeneral = array_sum(array_column($dons, 'montant'));
$projets = $pdo->query("SELECT id, titre FROM projets ORDER BY titre")->fetchAll();

include '../includes/header.php';
?>

<h2>إدارة التبرعات</h2>

<div class="projets-toolbar">
    <p><strong><?= count($dons) ?></strong>التبرعات — الإجمالي:<strong><?= number_format($totalGeneral, 2) ?> MAD</strong></p>

    <form method="GET" class="filter-form">
        <select name="projet_id" onchange="this.form.submit()">
            <option value="">جميع المشاريع</option>
            <?php foreach ($projets as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $filtreProjet == $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['titre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<table class="rh-table">
    <thead>
        <tr><th>التاريخ</th><th>المتبرع</th><th>البريد الإلكتروني</th><th>المشروع</th><th>المبلغ</th><th>طريقة الدفع</th></tr>
    </thead>
    <tbody>
    <?php foreach ($dons as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['date_don']) ?></td>
            <td><?= htmlspecialchars($d['nom_donateur']) ?></td>
            <td><?= htmlspecialchars($d['email_donateur'] ?? '-') ?></td>
            <td><?= htmlspecialchars($d['titre']) ?></td>
            <td><?= number_format($d['montant'], 2) ?> MAD</td>
            <td><?= htmlspecialchars($d['mode_paiement'] ?? '-') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($dons)): ?>
        <tr><td colspan="6">(لا يوجد أي تبرع حالياً)</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
