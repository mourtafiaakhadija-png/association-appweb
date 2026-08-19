<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/auth_check_benevole.php';
require_once '../includes/i18n_admin.php';
require_once '../config/db.php';

$benevoleId = $_SESSION['benevole_id'];
$projetId = (int) ($_GET['projet_id'] ?? 0);

// Vérification de sécurité IMPORTANTE : ce bénévole doit être le responsable de ce projet précis,
// sinon il pourrait modifier n'importe quel projet juste en changeant le numéro dans l'URL
$stmtProjet = $pdo->prepare("SELECT * FROM projets WHERE id = ? AND responsable_id = ?");
$stmtProjet->execute([$projetId, $benevoleId]);
$projet = $stmtProjet->fetch();

if (!$projet) {
    die("غير مسموح لك بالوصول إلى هذا المشروع.");
}

// Envoyer une édition (brouillon) pour validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['soumettre'])) {
    verifierJetonCsrf();
    $editionId = (int) $_POST['soumettre'];
    $pdo->prepare(
        "UPDATE projet_editions SET statut = 'en_attente_validation' 
         WHERE id = ? AND projet_id = ? AND statut IN ('brouillon', 'a_corriger')"
    )->execute([$editionId, $projetId]);
    header('Location: projet_editions.php?projet_id=' . $projetId);
    exit;
}

// Suppression d'un brouillon (uniquement s'il n'a pas encore été envoyé)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    verifierJetonCsrf();
    $pdo->prepare(
        "DELETE FROM projet_editions WHERE id = ? AND projet_id = ? AND statut = 'brouillon'"
    )->execute([(int) $_POST['delete'], $projetId]);
    header('Location: projet_editions.php?projet_id=' . $projetId);
    exit;
}

$editions = $pdo->prepare("SELECT * FROM projet_editions WHERE projet_id = ? ORDER BY numero_edition DESC");
$editions->execute([$projetId]);
$editions = $editions->fetchAll();

include '../includes/header_benevole.php';
?>

<h2>إصدارات — <?= htmlspecialchars($projet['titre']) ?></h2>
<p><a href="index.php">← رجوع للوحتي</a></p>

<a class="btn-add" href="projet_edition_form.php?projet_id=<?= $projetId ?>">+ إصدار جديد</a>

<table class="rh-table">
    <thead>
        <tr><th>الإصدار</th><th>الميزانية</th><th>الحالة</th><th>الإجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($editions as $e): ?>
        <tr>
            <td>#<?= $e['numero_edition'] ?></td>
            <td><?= number_format($e['budget_collecte'], 0) ?> / <?= number_format($e['budget_prevu'], 0) ?> MAD</td>
            <td>
                <span class="badge badge-<?= $e['statut'] ?>"><?= label('statut_edition', $e['statut']) ?></span>
                <?php if ($e['statut'] === 'a_corriger' && $e['commentaire_admin']): ?>
                    <p class="error" style="margin-top:0.4rem;"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($e['commentaire_admin']) ?></p>
                <?php endif; ?>
            </td>
            <td>
                <div class="rapport-actions">
                    <?php if (in_array($e['statut'], ['brouillon', 'a_corriger'])): ?>
                        <a href="projet_edition_form.php?id=<?= $e['id'] ?>" class="btn-mini">✏️ تعديل</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('إرسال هذا الإصدار للإدارة للمصادقة عليه؟');">
                            <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                            <input type="hidden" name="projet_id" value="<?= $projetId ?>">
                            <input type="hidden" name="soumettre" value="<?= $e['id'] ?>">
                            <button type="submit" class="btn-mini btn-accept">📤 إرسال للمصادقة</button>
                        </form>
                        <?php if ($e['statut'] === 'brouillon'): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('حذف هذه المسودة؟');">
                                <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                                <input type="hidden" name="delete" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn-mini btn-reject">🗑️ حذف</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="projet_edition_form.php?id=<?= $e['id'] ?>" class="btn-mini">👁️ عرض / إضافة رابور</a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($editions)): ?>
        <tr><td colspan="4">لا يوجد أي إصدار بعد.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer_benevole.php'; ?>