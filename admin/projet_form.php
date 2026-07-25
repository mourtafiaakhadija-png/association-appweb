<?php
session_start();
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isEdit = $id !== null;

$data = [
    'titre' => '', 'categorie_id' => '', 'responsable_id' => '',
    'cible_type' => 'famille', 'cible_details' => '', 'statut' => 'en_cours'
];

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) die("Projet introuvable.");
}

$categories = $pdo->query("SELECT * FROM categories_projets ORDER BY nom")->fetchAll();
$responsables = $pdo->query(
    "SELECT id, nom, prenom, role FROM users WHERE role IN ('bureau','benevole') ORDER BY nom"
)->fetchAll();

include '../includes/header.php';
?>

<h2><?= $isEdit ? 'تعديل المشروع' : 'Nouveau projet' ?></h2>
<p class="info-note">هذه هي البطاقة العامة للمشروع (هويته). أما المحتوى الملموس — الوصف، الميزانية، الصور، والتواريخ — فيتم تدبيره عبر كل نسخة (نسخة المشروع)، بمجرد إنشاء المشروع.</p>

<form method="POST" action="projet_action.php" class="projet-form">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $data['id'] ?>"><?php endif; ?>

    <label>اسم المشروع</label>
    <input type="text" name="titre" value="<?= htmlspecialchars($data['titre']) ?>" required placeholder="Ex: كفالة اليتيم">

    <div class="form-row">
        <div>
            <label>التصنيف</label>
            <select name="categorie_id">
                <option value="">-- اختر --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $data['categorie_id'] == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>المشرف</label>
            <select name="responsable_id">
                <option value="">-- لا أحد --</option>
                <?php foreach ($responsables as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $data['responsable_id'] == $r['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?> (<?= $r['role'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>نوع الفئة المستهدفة</label>
            <select name="cible_type" required>
                <option value="الأسرة" <?= $data['cible_type'] === 'famille' ? 'selected' : '' ?>>الأسرة</option>
                <option value="القرية" <?= $data['cible_type'] === 'village' ? 'selected' : '' ?>>القرية</option>
                <option value="المدرسة" <?= $data['cible_type'] === 'ecole' ? 'selected' : '' ?>>المدرسة</option>
                <option value="اليتيم" <?= $data['cible_type'] === 'orphelin' ? 'selected' : '' ?>>اليتيم</option>
            </select>
        </div>
        <div>
            <label>تفاصيل الفئة المستهدفة</label>
            <input type="text" name="cible_details" value="<?= htmlspecialchars($data['cible_details'] ?? '') ?>" placeholder="Ex: 120 orphelins, région de Taroudant">
        </div>
    </div>

    <label>الحالة العامة للمشروع</label>
    <select name="statut" required>
        <option value="مستمر" <?= $data['statut'] === 'en_cours' ? 'selected' : '' ?>>مستمر</option>
        <option value="انتهى" <?= $data['statut'] === 'termine' ? 'selected' : '' ?>>انتهى(الحمدلله)</option>
        <option value="معلق" <?= $data['statut'] === 'suspendu' ? 'selected' : '' ?>>معلق</option>
    </select>

    <button type="submit"><?= $isEdit ? 'حفظ التعديلات' : 'إنشاء المشروع' ?></button>
    <a href="projets.php" class="btn-cancel">إلغاء</a>
</form>

<?php if ($isEdit): ?>
    <p style="margin-top:1.5rem;">
        <a href="projet_editions.php?projet_id=<?= $id ?>" class="btn-add"><i class="fa-solid fa-folder-open"></i> إدارة نسخ هذا المشروع</a>
    </p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>