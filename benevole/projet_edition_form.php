<?php
session_start();
require_once '../includes/i18n_admin.php';
require_once '../includes/csrf.php';
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';
require_once '../includes/upload_helper.php';

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
    $stmtNum = $pdo->prepare("SELECT COALESCE(MAX(numero_edition), 0) + 1 FROM projet_editions WHERE projet_id = ?");
    $stmtNum->execute([$projetId]);
    $prochainNumero = (int) $stmtNum->fetchColumn();
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
$misesAJour = [];
$photosByMaj = [];
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM mises_a_jour_edition WHERE edition_id = ? ORDER BY date_ajout DESC");
    $stmt->execute([$id]);
    $misesAJour = $stmt->fetchAll();

    $stmtP = $pdo->prepare("SELECT * FROM photos_projets WHERE edition_id = ? AND maj_id IS NOT NULL ORDER BY date_ajout");
    $stmtP->execute([$id]);
    foreach ($stmtP->fetchAll() as $ph) {
        $photosByMaj[$ph['maj_id']][] = $ph;
    }
}

include '../includes/header_benevole.php';
?>

<h2><?= $isEdit ? 'تعديل الإصدار #' . $data['numero_edition'] : 'إصدار جديد' ?> — <?= htmlspecialchars($projet['titre']) ?></h2>

<?php if (!$modifiable): ?>
    <p class="info-note">تمت المصادقة على هذا الإصدار وهو منشور. لا يمكنك تعديل محتواه، لكن يمكنك إضافة أو تغيير ملف الرابور أسفله أو إضافة تحديث جديد.</p>
<?php endif; ?>

<form method="POST" action="projet_edition_action.php" enctype="multipart/form-data" class="projet-form" id="editionForm">
    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $data['id'] ?>"><?php endif; ?>
    <input type="hidden" name="projet_id" value="<?= $projetId ?>">

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
    <?php endif; ?>
</form>
<?php if ($isEdit && !empty($photos)): ?>
    <div class="existing-photos">
        <?php foreach ($photos as $ph): ?>
            <div class="existing-photo">
                <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>" alt="صورة" class="existing-photo-img" onerror="this.classList.add('broken-img')">
                <form method="POST" action="projet_edition_action.php" style="display:inline;" onsubmit="return confirm('حذف هذه الصورة؟');">
                    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                    <input type="hidden" name="delete_photo" value="<?= $ph['id'] ?>">
                    <input type="hidden" name="edition_id" value="<?= $id ?>">
                    <button type="submit" class="link-button">✕</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<div class="projet-form" style="margin-top:1.5rem;" > 
    <div class="form-section">
        <label>ملف الرابور (Word أو PDF)</label>
        <?php if ($isEdit && $data['fichier_rapport']): ?>
            <p class="info-note"><i class="fa-solid fa-file-lines"></i> ملف حالي: <a href="../uploads/<?= htmlspecialchars($data['fichier_rapport']) ?>" target="_blank">تحميل</a></p>
        <?php endif; ?>
        <input type="file" name="rapport" accept=".pdf,.doc,.docx" form="editionForm">
    </div>

    <div class="form-actions">
        <a href="projet_editions.php?projet_id=<?= $projetId ?>" class="btn-cancel">إلغاء</a>
        <div class="form-actions-right">
            <button type="submit" form="editionForm" name="action" value="enregistrer" class="btn-save">💾 حفظ (بدون إرسال)</button>
            <?php if ($modifiable): ?>
                <button type="submit" form="editionForm" name="action" value="soumettre" class="btn-submit-validation">📤 إرسال للمصادقة</button>
            <?php endif; ?>
        </div>
        
    </div>
</div>
<?php if ($isEdit): ?>
<hr>
<h3 style="margin-bottom:0.8rem; margin-top:1.5rem;">سجل التحديثات (Evolution) لهذا الإصدار</h3>
<p class="info-note">أضيفوا هنا تحديثا جديدا (وصف و/أو صور) لهذا الإصدار، بدون حذف أي شيء سابق. كل تحديث يحتاج مصادقة الإدارة قبل أن يظهر للعموم.</p>

<?php if (!empty($misesAJour)): ?>
<div class="maj-liste">
    <?php foreach ($misesAJour as $maj): ?>
        <div class="maj-item">
            <p class="maj-date">
                <?= date('d/m/Y', strtotime($maj['date_ajout'])) ?> —
                <?php if ($maj['statut'] === 'validee'): ?>
                    <span class="badge badge-validee">✅ منشور</span>
                <?php elseif ($maj['statut'] === 'a_corriger'): ?>
                    <span class="badge badge-a_corriger">⚠️ يحتاج تصحيح</span>
                <?php else: ?>
                    <span class="badge badge-en_attente_validation">⏳ في انتظار المراجعة</span>
                <?php endif; ?>
            </p>
            <p><?= nl2br(htmlspecialchars($maj['contenu'])) ?></p>
            <?php if (!empty($photosByMaj[$maj['id']])): ?>
                <p class="maj-photos-count">📷 <?= count($photosByMaj[$maj['id']]) ?> صورة مرفقة</p>
            <?php endif; ?>
            <?php if ($maj['statut'] === 'a_corriger' && $maj['commentaire_admin']): ?>
                <div class="rapport-comment"><?= htmlspecialchars($maj['commentaire_admin']) ?></div>
            <?php endif; ?>
            <?php if (in_array($maj['statut'], ['en_attente', 'a_corriger'])): ?>
                <div class="rapport-actions" style="margin-top:0.6rem;">
                    <a href="#" class="btn-mini" onclick="document.getElementById('edit-maj-<?= $maj['id'] ?>').style.display='block'; return false;">✏️ تعديل</a>
                    <form method="POST" action="projet_maj_action.php" style="display:inline;" onsubmit="return confirm('حذف هذا التحديث؟');">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="delete_maj_id" value="<?= $maj['id'] ?>">
                        <input type="hidden" name="edition_id" value="<?= $id ?>">
                        <button type="submit" class="btn-mini btn-reject">🗑️ حذف</button>
                    </form>
                </div>
                <form method="POST" action="projet_maj_action.php" enctype="multipart/form-data" id="edit-maj-<?= $maj['id'] ?>" class="renvoi-form" style="display:none;">
                    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                    <input type="hidden" name="update_maj_id" value="<?= $maj['id'] ?>">
                    <input type="hidden" name="edition_id" value="<?= $id ?>">
                    <label class="maj-edit-label">تعديل النص</label>
                    <textarea name="contenu" rows="4" required><?= htmlspecialchars($maj['contenu']) ?></textarea>
                    <label class="maj-edit-label">إضافة صور جديدة (اختياري، فوق الصور الحالية)</label>
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="maj-edit-file">
                    <button type="submit" class="btn-mini btn-accept" style="margin-top:0.6rem;">💾 حفظ التعديل</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" action="projet_maj_action.php" enctype="multipart/form-data" class="projet-form maj-form-add">
    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
    <input type="hidden" name="edition_id" value="<?= $id ?>">

    <label>➕ إضافة تحديث جديد</label>
    <textarea name="contenu" rows="4" placeholder="اكتبوا هنا آخر مستجدات هذا الإصدار..." required></textarea>

    <label>صور مرفقة بهذا التحديث (اختياري)</label>
    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="maj-edit-file">

    <button type="submit" class="btn-submit-validation" style="margin-top:1rem;">إرسال التحديث</button>
</form>
<?php endif; ?>

<?php include '../includes/footer_benevole.php'; ?>