<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$type = $_GET['type'] ?? 'bureau'; // 'bureau' ou 'collaborateur'
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isEdit = $id !== null;

$data = ['nom' => '', 'prenom' => '', 'email' => '', 'fonction' => '', 'bio' => '', 'description' => ''];

if ($isEdit) {
    if ($type === 'bureau') {
        $stmt = $pdo->prepare(
            "SELECT bm.id, bm.fonction, bm.bio, bm.photo, u.id as user_id, u.nom, u.prenom, u.email 
             FROM bureau_membres bm JOIN users u ON bm.user_id = u.id WHERE bm.id = ?"
        );
    } else {
        $stmt = $pdo->prepare("SELECT * FROM collaborateurs WHERE id = ?");
    }
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) {
        die("Élément introuvable.");
    }
}

include '../includes/header.php';
?>

<h2><?= $isEdit ? 'تعديل' : 'اظافة' ?> <?= $type === 'المكتب' ? 'عضو في المكتب' : 'متعاون' ?></h2>

<form method="POST" action="rh_action.php" enctype="multipart/form-data" class="rh-form">
    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
        <?php if ($type === 'bureau'): ?>
            <input type="hidden" name="user_id" value="<?= $data['user_id'] ?>">
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($type === 'bureau'): ?>
        <label>الاسم الشخصي</label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($data['prenom']) ?>" required>

        <label>الاسم العائلي</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($data['nom']) ?>" required>

        <label>البريد الإلكتروني</label>
        <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>

        <label>الصفة/الدور</label>
        <input type="text" name="fonction" value="<?= htmlspecialchars($data['fonction']) ?>" placeholder="رئيس, كاتب..." required>

        <label>Bio</label>
        <textarea name="bio" rows="4"><?= htmlspecialchars($data['bio'] ?? '') ?></textarea>

        <label>الصورة <?= $isEdit ? '(laisser vide pour ne pas changer)' : '' ?></label>
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
        <?php if ($isEdit && !empty($data['photo'])): ?>
            <img src="../uploads/<?= htmlspecialchars($data['photo']) ?>" class="thumb-preview">
        <?php endif; ?>

    <?php else: ?>
        <label>اسم المتعاون</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($data['nom']) ?>" required>

        <label>الوصف</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>

        <label>Logo <?= $isEdit ? '(laisser vide pour ne pas changer)' : '' ?></label>
        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
        <?php if ($isEdit && !empty($data['logo'])): ?>
            <img src="../uploads/<?= htmlspecialchars($data['logo']) ?>" class="thumb-preview">
        <?php endif; ?>
    <?php endif; ?>

    <button type="submit"><?= $isEdit ? 'حفظ التعديلات' : 'اظافة' ?></button>
    <a href="rh.php?tab=<?= $type === 'bureau' ? 'bureau' : 'collaborateurs' ?>" class="btn-cancel">رجوع</a>
</form>

<?php include '../includes/footer.php'; ?>
