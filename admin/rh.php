<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$tab = $_GET['tab'] ?? 'bureau';

// Suppression (depuis les liens "Supprimer")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && isset($_POST['type'])) {
    verifierJetonCsrf();
    $id = (int) $_POST['delete'];
    if ($_POST['type'] === 'bureau') {
        // On récupère le user_id lié pour supprimer aussi le compte user
        $stmt = $pdo->prepare("SELECT user_id FROM bureau_membres WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$row['user_id']]);
            // bureau_membres est supprimé automatiquement via ON DELETE CASCADE
        }
    } elseif ($_POST['type'] === 'collaborateur') {
        $pdo->prepare("DELETE FROM collaborateurs WHERE id = ?")->execute([$id]);
    }
    header('Location: rh.php?tab=' . urlencode($tab));
    exit;
}

// Récupération des données selon l'onglet actif
$bureau = $pdo->query(
    "SELECT bm.id, bm.fonction, bm.photo, bm.bio, u.nom, u.prenom, u.email 
     FROM bureau_membres bm JOIN users u ON bm.user_id = u.id 
     ORDER BY u.nom"
)->fetchAll();

$benevoles = $pdo->query(
    "SELECT id, nom, prenom, email, telephone, statut, created_at 
     FROM users WHERE role = 'benevole' ORDER BY nom"
)->fetchAll();

$donateurs = $pdo->query(
    "SELECT DISTINCT nom_donateur, email_donateur, COUNT(*) as nb_dons, SUM(montant) as total 
     FROM dons GROUP BY nom_donateur, email_donateur ORDER BY total DESC"
)->fetchAll();

$collaborateurs = $pdo->query("SELECT * FROM collaborateurs ORDER BY nom")->fetchAll();

include '../includes/header.php';
?>

<h2>إدارة الموارد البشرية</h2>

<nav class="rh-tabs">
    <a href="?tab=bureau" class="<?= $tab === 'bureau' ? 'active' : '' ?>">المكتب (<?= count($bureau) ?>)</a>
    <a href="?tab=benevoles" class="<?= $tab === 'benevoles' ? 'active' : '' ?>">المتطوعون (<?= count($benevoles) ?>)</a>
    <a href="?tab=donateurs" class="<?= $tab === 'donateurs' ? 'active' : '' ?>">المتبرعون (<?= count($donateurs) ?>)</a>
    <a href="?tab=collaborateurs" class="<?= $tab === 'collaborateurs' ? 'active' : '' ?>">المتعاونون (<?= count($collaborateurs) ?>)</a>
</nav>

<?php if ($tab === 'bureau'): ?>
    <a class="btn-add" href="rh_form.php?type=bureau"> إضافة عضو في المكتب +</a>
    <table class="rh-table">
        <thead>
            <tr><th>الصورة</th><th>الاسم</th><th> الصفة/ المنصب</th><th>البريد الإلكتروني</th><th>الإجراءات</th></tr>
        </thead>
        <tbody>
        <?php foreach ($bureau as $m): ?>
            <tr>
                <td><?php if ($m['photo']): ?><img src="../uploads/<?= htmlspecialchars($m['photo']) ?>" class="thumb"><?php endif; ?></td>
                <td><?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></td>
                <td><?= htmlspecialchars($m['fonction']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td>
                    <a href="rh_form.php?type=bureau&id=<?= $m['id'] ?>">تعديل</a> |
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce membre ?');">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="delete" value="<?= $m['id'] ?>">
                        <input type="hidden" name="type" value="bureau">
                        <button type="submit" class="link-button">حذف</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($bureau)): ?><tr><td colspan="5">لا يوجد أي عضو في المكتب حالياً .</td></tr><?php endif; ?>
        </tbody>
    </table>

<?php elseif ($tab === 'benevoles'): ?>
    <p class="info-note">يظهر المتطوعون هنا تلقائياً بعد قبول طلبات ترشحهم </p>
    <table class="rh-table">
        <thead><tr><th>الاسم</th><th>البريد الإلكتروني</th><th>الهاتف</th><th>الحالة</th><th>مسجل في</th></tr></thead>
        <tbody>
        <?php foreach ($benevoles as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['prenom'] . ' ' . $b['nom']) ?></td>
                <td><?= htmlspecialchars($b['email']) ?></td>
                <td><?= htmlspecialchars($b['telephone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($b['statut']) ?></td>
                <td><?= htmlspecialchars($b['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($benevoles)): ?><tr><td colspan="5">لا يوجد أي متطوع حالياً</td></tr><?php endif; ?>
        </tbody>
    </table>

<?php elseif ($tab === 'donateurs'): ?>
    <p class="info-note">.يظهر المتبرعون هنا تلقائياً بمجرد تسجيل تبرع جديد</p>
    <table class="rh-table">
        <thead><tr><th>الاسم</th><th>البريد الإلكتروني</th><th>عدد التبرعات</th><th>المجموع المقدم</th></tr></thead>
        <tbody>
        <?php foreach ($donateurs as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['nom_donateur']) ?></td>
                <td><?= htmlspecialchars($d['email_donateur'] ?? '-') ?></td>
                <td><?= (int) $d['nb_dons'] ?></td>
                <td><?= number_format((float) $d['total'], 2) ?> MAD</td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($donateurs)): ?><tr><td colspan="4">لا يوجد أي تبرع في الوقت الحالي</td></tr><?php endif; ?>
        </tbody>
    </table>

<?php elseif ($tab === 'collaborateurs'): ?>
    <a class="btn-add" href="rh_form.php?type=collaborateur"> إضافة متعاون +</a>
    <table class="rh-table">
        <thead><tr><th>Logo</th><th>الاسم</th><th>الوصف</th><th>الإجراءات</th></tr></thead>
        <tbody>
        <?php foreach ($collaborateurs as $c): ?>
            <tr>
                <td><?php if ($c['logo']): ?><img src="../uploads/<?= htmlspecialchars($c['logo']) ?>" class="thumb"><?php endif; ?></td>
                <td><?= htmlspecialchars($c['nom']) ?></td>
                <td><?= htmlspecialchars($c['description'] ?? '-') ?></td>
                <td>
                    <a href="rh_form.php?type=collaborateur&id=<?= $c['id'] ?>">تعديل</a> |
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce collaborateur ?');">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="delete" value="<?= $c['id'] ?>">
                        <input type="hidden" name="type" value="collaborateur">
                        <button type="submit" class="link-button">حذف</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($collaborateurs)): ?><tr><td colspan="4">لا يوجد متعاونون في الوقت الحالي</td></tr><?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
