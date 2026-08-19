<?php
require_once '../config/db.php';
require_once '../includes/notifications.php';

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if ($email && $token && hash_equals(genererJetonDesabonnement($email), $token)) {
    $pdo->prepare(
        "INSERT INTO donateurs_desabonnes (email) VALUES (?) ON DUPLICATE KEY UPDATE date_desabonnement = NOW()"
    )->execute([$email]);
    $message = "تم إلغاء اشتراككم بنجاح. لن تتوصلوا بعد الآن بإشعارات المشاريع.";
} else {
    $message = "رابط غير صالح.";
}

$pageTitle = 'إلغاء الاشتراك';
include '../includes/header_public.php';
?>
<section class="section">
    <div class="container" style="text-align:center; padding: 3rem 0;">
        <p class="info-note"><?= htmlspecialchars($message) ?></p>
        <a href="index.php" class="btn-outline-white">العودة إلى الموقع</a>
    </div>
</section>
<?php include '../includes/footer_public.php'; ?>