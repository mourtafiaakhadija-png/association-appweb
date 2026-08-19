<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isEdit = $id !== null;

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM projet_editions WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) die("Édition introuvable.");
    $projetId = $data['projet_id'];
} else {
    $projetId = (int) ($_GET['projet_id'] ?? 0);
    $stmtNum = $pdo->prepare("SELECT COALESCE(MAX(numero_edition), 0) + 1 FROM projet_editions WHERE projet_id = ?");
    $stmtNum->execute([$projetId]);
    $prochainNumero = (int) $stmtNum->fetchColumn();
    $data = [
        'numero_edition' => $prochainNumero, 'description' => '', 'budget_prevu' => '', 'budget_collecte' => 0,
        'date_debut' => '', 'date_fin' => '', 'statut' => 'validee', 'a_la_une' => 0, 'appel_benevoles_ouvert' => 0,
    ];
}

$stmtProjet = $pdo->prepare("SELECT titre FROM projets WHERE id = ?");
$stmtProjet->execute([$projetId]);
$projet = $stmtProjet->fetch();

$photos = [];
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM photos_projets WHERE edition_id = ? ORDER BY date_ajout");
    $stmt->execute([$id]);
    $photos = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<h2><?= $isEdit ? 'تعديل الإصدار #' . $data['numero_edition'] : 'إصدار جديد' ?> — <?= htmlspecialchars($projet['titre']) ?></h2>

<?php if ($isEdit && $data['fichier_rapport']): ?>
    <p class="info-note"><i class="fa-regular fa-file-lines"></i> التقرير المرسل من طرف رئيس النشاط: <a href="../uploads/<?= htmlspecialchars($data['fichier_rapport']) ?>" target="_blank">تحميل</a></p>
<?php endif; ?>

<?php if ($isEdit && $data['statut'] === 'a_corriger' && $data['commentaire_admin']): ?>
    <p class="error"><i class="fa-solid fa-triangle-exclamation" style= "color: #993104f0"></i> تمت الإعادة للتصحيح — تعليق:<?= htmlspecialchars($data['commentaire_admin']) ?></p>
<?php endif; ?>

<form method="POST" action="projet_edition_action.php" enctype="multipart/form-data" class="projet-form" id="editionForm">
    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
    <input type="hidden" name="projet_id" value="<?= $projetId ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $data['id'] ?>"><?php endif; ?>

    <label>رقم / سنة الإصدار</label>
    <input type="number" name="numero_edition" value="<?= htmlspecialchars($data['numero_edition']) ?>" required>

    <label>الوصف</label>
    <textarea name="description" rows="5" required><?= htmlspecialchars($data['description']) ?></textarea>

    <div class="form-row">
        <div>
            <label>الميزانية التقديرية (درهم)</label>
            <input type="number" step="0.01" name="budget_prevu" value="<?= htmlspecialchars($data['budget_prevu']) ?>" required>
        </div>
        <div>
            <label>الميزانية الجارية (درهم)</label>
            <input type="number" step="0.01" name="budget_collecte" value="<?= htmlspecialchars($data['budget_collecte']) ?>">
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>تاريخ البداية</label>
            <input type="date" name="date_debut" value="<?= htmlspecialchars($data['date_debut']) ?>">
        </div>
        <div>
            <label>تاريخ النهاية</label>
            <input type="date" name="date_fin" value="<?= htmlspecialchars($data['date_fin']) ?>">
        </div>
    </div>

    <label>الحالة</label>
    <select name="statut" required>
        <option value="brouillon" <?= $data['statut'] === 'brouillon' ? 'selected' : '' ?>>مسودة (غير منشورة بعد)</option>
        <option value="validee" <?= $data['statut'] === 'validee' ? 'selected' : '' ?>>مُصادق عليها (مرئية للعموم)</option>
    </select>

    <div class="form-row">
        <div>
            <label><input type="checkbox" name="a_la_une" value="1" <?= $data['a_la_une'] ? 'checked' : '' ?>> وضع في الواجهة (الصفحة الرئيسية)</label>
        </div>
        <div>
            <label><input type="checkbox" name="appel_benevoles_ouvert" value="1" <?= $data['appel_benevoles_ouvert'] ? 'checked' : '' ?>> فتح باب التطوع لهذا الإصدار</label>
        </div>
    </div>

    <label>إضافة صور <?= $isEdit ? '(بالإضافة إلى الصور الموجودة)' : '' ?></label>
    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
</form>
<?php if ($isEdit && !empty($photos)): ?>
    <div class="existing-photos">
        <?php foreach ($photos as $ph): ?>
            <div class="existing-photo">
                <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>">
                <form method="POST" action="projet_edition_action.php" style="display:inline;" onsubmit="return confirm('حذف هذه الصورة؟');">
                    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                    <input type="hidden" name="delete_photo" value="<?= $ph['id'] ?>">
                    <input type="hidden" name="edition_id" value="<?= $id ?>">
                    <button type="submit" class="link-button">✕ حذف</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<div class="container">
    <button type="submit" class=" btn_adminprojet" form="editionForm"><?= $isEdit ? 'حفظ ' : 'إظافة النسخة ' ?></button>
    <a href="projet_editions.php?projet_id=<?= $projetId ?>" class="btn_adminprojet">إلغاء</a>
</div>


<?php include '../includes/footer.php'; ?>