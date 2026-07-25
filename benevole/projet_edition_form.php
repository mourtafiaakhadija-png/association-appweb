<?php
session_start();
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';

$benevoleId = $_SESSION['benevole_id'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isEdit = $id !== null;

if ($isEdit) {
    $stmt = $pdo->prepare(
        "SELECT e.* FROM projet_editions e 
         JOIN projets p ON p.id = e.projet_id 
         WHERE e.id = ? AND p.responsable_id = ?"
    );
    $stmt->execute([$id, $benevoleId]);
    $data = $stmt->fetch();
    if (!$data) die("غير مسموح لك بالوصول إلى هذا الإصدار.");
    $projetId = $data['projet_id'];

    // Un bénévole ne modifie plus une édition déjà envoyée ou déjà validée
    // (sauf pour y ajouter un rapport une fois publiée — géré plus bas)
    $modifiable = in_array($data['statut'], ['brouillon', 'a_corriger']);
} else {
    $projetId = (int) ($_GET['projet_id'] ?? 0);
    $modifiable = true;
}

// Vérification de sécurité : le projet doit bien appartenir à ce bénévole
$stmtProjet = $pdo->prepare("SELECT titre FROM projets WHERE id = ? AND responsable_id = ?");
$stmtProjet->execute([$projetId, $benevoleId]);
$projet = $stmtProjet->fetch();
if (!$projet) die("غير مسموح لك بالوصول إلى هذا المشروع.");

if (!$isEdit) {
    $prochainNumero = (int) $pdo->query("SELECT COALESCE(MAX(numero_edition), 0) + 1 FROM projet_editions WHERE projet_id = $projetId")->fetchColumn();
    $data = [
        'numero_edition' => $prochainNumero, 'description' => '', 'budget_prevu' => '',
        'date_debut' => '', 'date_fin' => '', 'fichier_rapport' => null, 'statut' => 'brouillon',
    ];
}

$photos = [];
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM photos_projets WHERE edition_id = ? ORDER BY date_ajout");
    $stmt->execute([$id]);
    $photos = $stmt->fetchAll();
}

include '../includes/header_benevole.php';
?>

<h2><?= $isEdit ? 'تعديل الإصدار #' . $data['numero_edition'] : 'إصدار جديد' ?> — <?= htmlspecialchars($projet['titre']) ?></h2>

<?php if (!$modifiable): ?>
    <p class="info-note">تمت المصادقة على هذا الإصدار وهو منشور. لا يمكنك تعديل محتواه، لكن يمكنك إضافة أو تغيير ملف الرابور أسفله.</p>
<?php endif; ?>

<form method="POST" action="projet_edition_action.php" enctype="multipart/form-data" class="projet-form">
    <input type="hidden" name="projet_id" value="<?= $projetId ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $data['id'] ?>"><?php endif; ?>

    <?php if ($modifiable): ?>
        <label>رقم الإصدار</label>
        <input type="number" name="numero_edition" value="<?= htmlspecialchars($data['numero_edition']) ?>" required>

        <label>الوصف</label>
        <textarea name="description" rows="5" required><?= htmlspecialchars($data['description']) ?></textarea>

        <div class="form-row">
            <div>
                <label>الميزانية المتوقعة (MAD)</label>
                <input type="number" step="0.01" name="budget_prevu" value="<?= htmlspecialchars($data['budget_prevu']) ?>" required>
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

        <label>إضافة صور</label>
        <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>

        <?php if ($isEdit && !empty($photos)): ?>
            <div class="existing-photos">
                <?php foreach ($photos as $ph): ?>
                    <div class="existing-photo">
                        <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>">
                        <a href="projet_edition_action.php?delete_photo=<?= $ph['id'] ?>&edition_id=<?= $id ?>" onclick="return confirm('حذف هذه الصورة؟');">✕</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <label>ملف الرابور (Word أو PDF)</label>
    <?php if ($isEdit && $data['fichier_rapport']): ?>
        <p class="info-note"><i class="fa-solid fa-file-lines"></i> ملف حالي: <a href="../uploads/<?= htmlspecialchars($data['fichier_rapport']) ?>" target="_blank">تحميل</a></p>
    <?php endif; ?>
    <input type="file" name="rapport" accept=".pdf,.doc,.docx">

    <button type="submit">حفظ</button>
    <a href="projet_editions.php?projet_id=<?= $projetId ?>" class="btn-cancel">إلغاء</a>
</form>

<?php include '../includes/footer_benevole.php'; ?>