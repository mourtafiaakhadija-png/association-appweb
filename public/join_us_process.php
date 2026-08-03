<?php
require_once '../config/db.php';
require_once '../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: join_us.php');
    exit;
}

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$dateNaissance = $_POST['date_naissance'] ?? null;
$ville = trim($_POST['ville'] ?? '');
$profession = trim($_POST['profession'] ?? '');
$niveauEtude = trim($_POST['niveau_etude'] ?? '');
$competences = trim($_POST['competences'] ?? '');
$experiences = trim($_POST['experiences'] ?? '');
$motivation = trim($_POST['motivation'] ?? '');

// Validation serveur (seuls les champs obligatoires du formulaire sont vérifiés)
$errors = [];
if ($nom === '') $errors[] = "الاسم العائلي مطلوب";
if ($prenom === '') $errors[] = "الاسم الشخصي مطلوب";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "بريد إلكتروني غير صالح";
if ($motivation === '') $errors[] = "الدافع مطلوب";

// date_naissance est optionnelle : si vide, on la met à NULL plutôt qu'une chaîne vide
// (une chaîne vide '' ferait planter la colonne DATE de MySQL)
if ($dateNaissance === '') $dateNaissance = null;

if (!empty($errors)) {
    header('Location: join_us.php?error=1');
    exit;
}

try {
    $pdo->prepare(
        "INSERT INTO candidatures_benevoles 
         (nom, prenom, email, telephone, date_naissance, ville, profession, niveau_etude, competences, experiences, motivation) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    )->execute([$nom, $prenom, $email, $telephone, $dateNaissance, $ville, $profession, $niveauEtude, $competences, $experiences, $motivation]);

    // Email d'accusé de réception (le candidat aura une vraie réponse plus tard, via admin/candidature_action.php)
    $bodyHtml = "
        <div dir='rtl' style='font-family:Tajawal,Arial,sans-serif; max-width:600px; margin:0 auto;'>
            <h2 style='color:#E8622C;'>تم استلام ترشحكم </h2>
            <p>عزيزي/عزيزتي " . htmlspecialchars($prenom . ' ' . $nom) . "،</p>
            <p>شكرا على اهتمامكم بالانضمام إلى جمعية الجيل المبدع كمتطوعين.</p>
            <p>سيقوم فريقنا بدراسة ترشحكم، وستتوصلون بجواب عبر هذا البريد الإلكتروني في أقرب وقت.</p>
            <p style='color:#888; font-size:0.85rem; margin-top:2rem;'>جمعية الجيل المبدع — تارودانت</p>
        </div>
    ";
    sendMail($email, $prenom . ' ' . $nom, 'تأكيد استلام ترشحكم - جمعية الجيل المبدع', $bodyHtml);

    header('Location: join_us.php?success=1');
    exit;

} catch (Exception $e) {
    header('Location: join_us.php?error=1');
    exit;
}