<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$adminId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT nom, prenom, email FROM users WHERE id = ?");
$stmt->execute([$adminId]);
$user = $stmt->fetch();

$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierJetonCsrf();
    $motDePasseActuel = $_POST['mot_de_passe_actuel'] ?? '';
    $nouveauMotDePasse = $_POST['nouveau_mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    $stmtPwd = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmtPwd->execute([$adminId]);
    $hashActuel = $stmtPwd->fetchColumn();

    if (!password_verify($motDePasseActuel, $hashActuel)) {
        $erreur = 'كلمة المرور الحالية غير صحيحة.';
    } elseif (strlen($nouveauMotDePasse) < 8) {
        $erreur = 'كلمة المرور الجديدة يجب أن تحتوي على 8 خانات على الأقل.';
    } elseif ($nouveauMotDePasse !== $confirmation) {
        $erreur = 'كلمة المرور الجديدة وتأكيدها غير متطابقين.';
    } else {
        $nouveauHash = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$nouveauHash, $adminId]);
        $succes = 'تم تغيير كلمة المرور بنجاح.';
    }
}

include '../includes/header.php';
?>

<h2>حسابي</h2>

<div class="admin-profil-card" style="max-width:500px;">
    <p><strong>الاسم الكامل:</strong> <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></p>
    <p><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($user['email']) ?></p>
</div>

<h3 style="margin-top:2rem;">تغيير كلمة المرور</h3>
<p class="info-note">اختياري — يمكنكم الاحتفاظ بكلمة المرور الحالية إن أردتم.</p>

<?php if ($succes): ?><p class="info-note" style="background:#dcfce7; color:#166534;"><?= htmlspecialchars($succes) ?></p><?php endif; ?>
<?php if ($erreur): ?><p class="error"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>

<form method="POST" class="profil" style="max-width:500px;">
    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
    <label>كلمة المرور الحالية</label>
    <input type="password" name="mot_de_passe_actuel" required>

    <label>كلمة المرور الجديدة (8 خانات على الأقل)</label>
    <input type="password" name="nouveau_mot_de_passe" required minlength="8">

    <label>تأكيد كلمة المرور الجديدة</label>
    <input type="password" name="confirmation" required minlength="8">
    <br>
    <button type="submit">تغيير كلمة المرور</button>
</form>

<?php include '../includes/footer.php'; ?>