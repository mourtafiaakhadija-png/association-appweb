<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../includes/mailer.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: candidatures.php'); exit; }
verifierJetonCsrf();
$id = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id <= 0 || !in_array($action, ['accepter', 'rejeter'])) {
    header('Location: candidatures.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM candidatures_benevoles WHERE id = ?");
$stmt->execute([$id]);
$candidature = $stmt->fetch();

if (!$candidature || $candidature['statut'] !== 'en_attente') {
    header('Location: candidatures.php');
    exit;
}

try {
    if ($action === 'accepter') {

        // On vérifie qu'un compte avec cet email n'existe pas déjà (ex: ancien donateur, etc.)
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute([$candidature['email']]);
        if ($checkEmail->fetch()) {
            die("Un compte existe déjà avec cet email (" . htmlspecialchars($candidature['email']) . "). Impossible de créer un compte bénévole en double.");
        }

        $pdo->beginTransaction();

        // Mot de passe temporaire, en clair pour l'email, haché pour la base
        $tempPasswordClair = bin2hex(random_bytes(4)); // ex: "a1b2c3d4"
        $tempPasswordHash = password_hash($tempPasswordClair, PASSWORD_DEFAULT);

        $pdo->prepare(
            "INSERT INTO users (nom, prenom, email, password, role, telephone, statut) 
             VALUES (?, ?, ?, ?, 'benevole', ?, 'actif')"
        )->execute([
            $candidature['nom'],
            $candidature['prenom'],
            $candidature['email'],
            $tempPasswordHash,
            $candidature['telephone'],
        ]);

        $pdo->prepare(
            "UPDATE candidatures_benevoles SET statut = 'acceptee', date_reponse = NOW() WHERE id = ?"
        )->execute([$id]);

        $pdo->commit();

        $bodyHtml = "
            <div dir='rtl' style='font-family:Tajawal,Arial,sans-serif; max-width:600px; margin:0 auto;'>
                <h2 style='color:#1E3E8C;'>مبروك! تم قبول ترشحكم </h2>
                <p>عزيزي/عزيزتي " . htmlspecialchars($candidature['prenom'] . ' ' . $candidature['nom']) . "،</p>
                <p>يسعدنا إخباركم بأنه تم قبول ترشحكم للانضمام كمتطوعين ضمن جمعية الجيل المبدع.</p>
                <p>تم إنشاء حساب خاص بكم، وهذه معلومات الولوج:</p>
                <div style='background:#f4f5f7; padding:12px 16px; border-radius:8px; margin:1rem 0; direction:ltr; text-align:left;'>
                    <strong>Email:</strong> " . htmlspecialchars($candidature['email']) . "<br>
                    <strong>Mot de passe temporaire:</strong> " . htmlspecialchars($tempPasswordClair) . "
                </div>
                <p>ننصحكم بتغيير كلمة المرور بعد أول ولوج.</p>
                <p style='color:#888; font-size:0.85rem; margin-top:2rem;'>جمعية الجيل المبدع — تارودانت</p>
            </div>
        ";
        sendMail($candidature['email'], $candidature['prenom'] . ' ' . $candidature['nom'], 'تم قبول ترشحكم - جمعية الجيل المبدع', $bodyHtml);

    } elseif ($action === 'rejeter') {

        $pdo->prepare(
            "UPDATE candidatures_benevoles SET statut = 'rejetee', date_reponse = NOW() WHERE id = ?"
        )->execute([$id]);

        $bodyHtml = "
            <div dir='rtl' style='font-family:Tajawal,Arial,sans-serif; max-width:600px; margin:0 auto;'>
                <h2 style='color:#1E3E8C;'>بخصوص ترشحكم</h2>
                <p>عزيزي/عزيزتي " . htmlspecialchars($candidature['prenom'] . ' ' . $candidature['nom']) . "،</p>
                <p>نشكركم على اهتمامكم بالانضمام إلى جمعية الجيل المبدع كمتطوعين.</p>
                <p>بعد دراسة ترشحكم، نأسف لإخباركم أننا لا نستطيع قبوله في الوقت الحالي. نتمنى لكم التوفيق ونرحب بترشحكم مستقبلا.</p>
                <p style='color:#888; font-size:0.85rem; margin-top:2rem;'>جمعية الجيل المبدع — تارودانت</p>
            </div>
        ";
        sendMail($candidature['email'], $candidature['prenom'] . ' ' . $candidature['nom'], 'بخصوص ترشحكم - جمعية الجيل المبدع', $bodyHtml);
    }

    header('Location: candidatures.php');
    exit;

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    gererErreur($e, "حدث خطأ أثناء العملية. يرجى المحاولة مرة أخرى أو التواصل مع الإدارة.");
}