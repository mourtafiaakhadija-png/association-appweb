<?php
session_start();
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$editionId = (int) ($_GET['edition_id'] ?? 0);

$stmtEdition = $pdo->prepare(
    "SELECT e.*, p.titre FROM projet_editions e JOIN projets p ON p.id = e.projet_id WHERE e.id = ?"
);
$stmtEdition->execute([$editionId]);
$edition = $stmtEdition->fetch();
if (!$edition) die("الإصدار غير موجود.");

// Confirmer / retirer un membre
if (isset($_GET['confirmer'])) {
    $pdo->prepare("UPDATE participations_comite SET statut = 'confirme' WHERE id = ? AND edition_id = ?")
        ->execute([(int) $_GET['confirmer'], $editionId]);
    header('Location: comite_membres.php?edition_id=' . $editionId);
    exit;
}
if (isset($_GET['retirer'])) {
    $pdo->prepare("DELETE FROM participations_comite WHERE id = ? AND edition_id = ?")
        ->execute([(int) $_GET['retirer'], $editionId]);
    header('Location: comite_membres.php?edition_id=' . $editionId);
    exit;
}

$membres = $pdo->prepare(
    "SELECT pc.*, u.nom, u.prenom, u.email, u.telephone
     FROM participations_comite pc
     JOIN users u ON u.id = pc.user_id
     WHERE pc.edition_id = ?
     ORDER BY pc.statut, pc.date_reponse"
);
$membres->execute([$editionId]);
$membres = $membres->fetchAll();

include '../includes/header.php';
?>

<h2>فريق — <?= htmlspecialchars($edition['titre']) ?> (الإصدار #<?= $edition['numero_edition'] ?>)</h2>
<p><a href="comites.php">← رجوع لقائمة اللجان</a></p>

<table class="rh-table">
    <thead>
        <tr><th>الاسم</th><th>البريد الإلكتروني</th><th>الهاتف</th><th>الحالة</th><th>الإجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($membres as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></td>
            <td><?= htmlspecialchars($m['email']) ?></td>
            <td><?= htmlspecialchars($m['telephone'] ?: '-') ?></td>
            <td>
                <?php if ($m['statut'] === 'confirme'): ?>
                    <span class="badge badge-validee">✅ مؤكد</span>
                <?php else: ?>
                    <span class="badge badge-en_attente_validation">⏳ متوفر</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($m['statut'] !== 'confirme'): ?>
                    <a href="?edition_id=<?= $editionId ?>&confirmer=<?= $m['id'] ?>">تأكيد</a> |
                <?php endif; ?>
                <a href="?edition_id=<?= $editionId ?>&retirer=<?= $m['id'] ?>" onclick="return confirm('إزالة هذا العضو من اللائحة؟');">إزالة</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($membres)): ?>
        <tr><td colspan="5">لا يوجد أي متطوع مسجل بعد.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>