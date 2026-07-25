<?php
session_start();
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';

$benevoleId = $_SESSION['benevole_id'];

$stmt = $pdo->prepare("SELECT nom, prenom, email, telephone FROM users WHERE id = ?");
$stmt->execute([$benevoleId]);
$user = $stmt->fetch();

$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motDePasseActuel = $_POST['mot_de_passe_actuel'] ?? '';
    $nouveauMotDePasse = $_POST['nouveau_mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    $stmtPwd = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmtPwd->execute([$benevoleId]);
    $hashActuel = $stmtPwd->fetchColumn();

    if (!password_verify($motDePasseActuel, $hashActuel)) {
        $erreur = 'كلمة المرور الحالية غير صحيحة.';
    } elseif (strlen($nouveauMotDePasse) < 8) {
        $erreur = 'كلمة المرور الجديدة يجب أن تحتوي على 8 خانات على الأقل.';
    } elseif ($nouveauMotDePasse !== $confirmation) {
        $erreur = 'كلمة المرور الجديدة وتأكيدها غير متطابقين.';
    } else {
        $nouveauHash = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$nouveauHash, $benevoleId]);
        $succes = 'تم تغيير كلمة المرور بنجاح.';
    }
}

include '../includes/header_benevole.php';
?>

<h2>حسابي</h2>

<div class="benevole-projet-card" style="max-width:500px;">
    <p><strong>الاسم الكامل:</strong> <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></p>
    <p><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>الهاتف:</strong> <?= htmlspecialchars($user['telephone'] ?: '-') ?></p>
</div>

<h3 style="margin-top:2rem;">تغيير كلمة المرور</h3>
<p class="info-note">اختياري — يمكنكم الاحتفاظ بكلمة المرور الحالية إن أردتم.</p>

<?php if ($succes): ?><p class="info-note" style="background:#dcfce7; color:#166534;"><?= htmlspecialchars($succes) ?></p><?php endif; ?>
<?php if ($erreur): ?><p class="error"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>

<form method="POST" class="profil" style="max-width:500px;">
    <label>كلمة المرور الحالية</label>
    <input type="password" name="mot_de_passe_actuel" required>

    <label>كلمة المرور الجديدة</label>
    <input type="password" name="nouveau_mot_de_passe" required minlength="8">

    <label>تأكيد كلمة المرور الجديدة</label>
    <input type="password" name="confirmation" required minlength="8">
    </br>
    <button type="submit">تغيير كلمة المرور</button>
</form>

<?php include '../includes/footer_benevole.php'; ?>