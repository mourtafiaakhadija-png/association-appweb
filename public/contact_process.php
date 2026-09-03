<?php
require_once 'config/db.php';
require_once 'includes/csrf.php'; 
require_once 'includes/mailer.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}


verifierJetonCsrf();

if (!empty($_POST['website'])) {
    // Bot détecté silencieusement
    header('Location: contact.php?success=1'); 
    exit;
}
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$sujet = trim($_POST['sujet'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation serveur
$errors = [];
if ($nom === '') $errors[] = "الاسم مطلوب";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "بريد إلكتروني غير صالح";
if ($message === '') $errors[] = "الرسالة مطلوبة";

if (!empty($errors)) {
    header('Location: contact.php?error=1');
    exit;
}

try {
    $pdo->prepare(
        "INSERT INTO messages_contact (nom, email, sujet, message) VALUES (?, ?, ?, ?)"
    )->execute([$nom, $email, $sujet, $message]);

    // Email de confirmation à l'expéditeur (ne bloque jamais le processus si ça échoue)
    $bodyHtml = "
        <div dir='rtl' style='font-family:Tajawal,Arial,sans-serif; max-width:600px; margin:0 auto;'>
            <h2 style='color:#1E3E8C;'>تم استلام رسالتكم </h2>
            <p>عزيزي/عزيزتي " . htmlspecialchars($nom) . "،</p>
            <p>شكرا لتواصلكم معنا. لقد تلقينا رسالتكم وسنجيبكم في أقرب وقت ممكن.</p>
            <p style='color:#888; font-size:0.85rem; margin-top:2rem;'>جمعية الجيل المبدع — تارودانت</p>
        </div>
    ";
    sendMail($email, $nom, 'تأكيد استلام رسالتكم - جمعية الجيل المبدع', $bodyHtml);

    header('Location: contact.php?success=1');
    exit;

} catch (Exception $e) {
    header('Location: contact.php?error=1');
    exit;
}