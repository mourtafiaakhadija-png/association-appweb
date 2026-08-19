<?php
session_start();
require_once '../includes/login_throttle.php';
require_once '../config/db.php';
require_once '../includes/csrf.php';

if (isset($_SESSION['benevole_id'])) {
    header('Location: index.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['password'] ?? '';

    $minutesRestantes = estBloque($pdo, $email);
    if ($minutesRestantes > 0) {
        $erreur = "تم حظر هذا الحساب مؤقتا. حاولوا مجددا بعد $minutesRestantes دقيقة.";
    } else {
        $stmt = $pdo->prepare("SELECT id, nom, prenom, email, password, role, statut FROM users WHERE email = ? AND role = 'benevole'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

    if ($user && password_verify($motDePasse, $user['password'])) {
        if ($user['statut'] !== 'actif') {
            $erreur = 'هذا الحساب معطل حاليا. يرجى التواصل مع الإدارة.';
        } else {
            reinitialiserTentatives($pdo, $email);
            session_regenerate_id(true);
            $_SESSION['benevole_id'] = $user['id'];
            $_SESSION['benevole_nom'] = $user['nom'];
            $_SESSION['benevole_prenom'] = $user['prenom'];
            header('Location: index.php');
            exit;
        }
    } else {
        enregistrerEchec($pdo, $email);
        $erreur = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
    }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - فضاء المتطوع</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/benevole.css">
</head>
<body class="login-page">
    <div class="login-card">
        <h1>فضاء المتطوع</h1>
        <p class="login-subtitle">جمعية الجيل المبدع</p>
        <?php if (isset($_GET['desactive'])): ?>
            <p class="error">تم تسجيل خروجكم لأن هذا الحساب معطل حاليا.</p>
        <?php endif; ?>
        <?php if ($erreur): ?>
            <p class="error"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" required autofocus>

            <label>كلمة المرور</label>
            <input type="password" name="password" required>

            <button type="submit">تسجيل الدخول</button>
        </form>
    </div>
</body>
</html>