<?php
session_start();
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$projetId = (int) ($_GET['projet_id'] ?? 0);

$stmtProjet = $pdo->prepare("SELECT * FROM projets WHERE id = ?");
$stmtProjet->execute([$projetId]);
$projet = $stmtProjet->fetch();

if (!$projet) die("Projet introuvable.");

// Renvoyer une édition pour correction (avec commentaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renvoyer_edition_id'])) {
    $editionId = (int) $_POST['renvoyer_edition_id'];
    $commentaire = trim($_POST['commentaire_admin']);
    $pdo->prepare(
        "UPDATE projet_editions SET statut = 'a_corriger', commentaire_admin = ? WHERE id = ?"
    )->execute([$commentaire, $editionId]);
    header('Location: projet_editions.php?projet_id=' . $projetId);
    exit;
}

// Suppression d'une édition
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM projet_editions WHERE id = ? AND projet_id = ?")->execute([(int) $_GET['delete'], $projetId]);
    header('Location: projet_editions.php?projet_id=' . $projetId);
    exit;
}

$editions = $pdo->prepare("SELECT * FROM projet_editions WHERE projet_id = ? ORDER BY numero_edition DESC");
$editions->execute([$projetId]);
$editions = $editions->fetchAll();

$statutLabels = [
    'brouillon' => '📝 مسودة',
    'en_attente_validation' => '⏳ في انتظار التحقق',
    'a_corriger' => '⚠️ يحتاج إلى تصحيح (تمت إعادته للمتطوع)',
    'validee' => '✅ تمت التحقق (منشور)',
];

include '../includes/header.php';
?>

<h2>الإصدارات — <?= htmlspecialchars($projet['titre']) ?></h2>
<p><a href="projet_form.php?id=<?= $projetId ?>">← العودة إلى بطاقة المشروع</a></p>

<a class="btn-add" href="projet_edition_form.php?projet_id=<?= $projetId ?>">+ إصدار جديد</a>

<table class="rh-table">
    <thead>
        <tr><th>الإصدار</th><th>الميزانية</th><th>التواريخ</th><th>الحالة</th><th>في الواجهة</th><th>الإجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($editions as $e): ?>
        <tr>
            <td>#<?= $e['numero_edition'] ?></td>
            <td><?= number_format($e['budget_collecte'], 0) ?> / <?= number_format($e['budget_prevu'], 0) ?> MAD</td>
            <td><?= htmlspecialchars($e['date_debut'] ?: '-') ?> → <?= htmlspecialchars($e['date_fin'] ?: '-') ?></td>
            <td><?= $statutLabels[$e['statut']] ?? $e['statut'] ?></td>
            <td><?= $e['a_la_une'] ? '⭐ نعم' : '-' ?></td>
            <td>
                <a href="projet_edition_form.php?id=<?= $e['id'] ?>">تعديل</a> |
                <?php if ($e['statut'] === 'en_attente_validation'): ?>
                    <a href="#" onclick="document.getElementById('renvoi-<?= $e['id'] ?>').style.display='block'; return false;">إعادة للتصحيح</a> |
                <?php endif; ?>
                <a href="?projet_id=<?= $projetId ?>&delete=<?= $e['id'] ?>" onclick="return confirm('Supprimer cette édition et ses photos ?');">حذف</a>

                <?php if ($e['statut'] === 'en_attente_validation'): ?>
                <form method="POST" id="renvoi-<?= $e['id'] ?>" style="display:none; margin-top:0.5rem;">
                    <input type="hidden" name="renvoyer_edition_id" value="<?= $e['id'] ?>">
                    <textarea name="commentaire_admin" rows="2" placeholder="ما الذي يجب تصحيحه ?" required style="width:100%;"></textarea>
                    <button type="submit">إرسال</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($editions)): ?>
        <tr><td colspan="6">لا يوجد أي إصدار حالياً.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>