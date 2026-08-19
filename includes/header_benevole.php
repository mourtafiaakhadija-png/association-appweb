<?php
$stmtNotif = $pdo->prepare(
    "SELECT
        (SELECT COUNT(*) FROM projet_editions e JOIN projets p ON p.id = e.projet_id WHERE p.responsable_id = ? AND e.statut = 'a_corriger') +
        (SELECT COUNT(*) FROM projet_editions e JOIN projets p ON p.id = e.projet_id WHERE p.responsable_id = ? AND e.rapport_statut = 'a_corriger') +
        (SELECT COUNT(*) FROM mises_a_jour_edition m JOIN projet_editions e ON e.id = m.edition_id JOIN projets p ON p.id = e.projet_id WHERE p.responsable_id = ? AND m.statut = 'a_corriger')
    AS total"
);
$stmtNotif->execute([$_SESSION['benevole_id'], $_SESSION['benevole_id'], $_SESSION['benevole_id']]);
$nbNotifications = (int) $stmtNotif->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فضاء المتطوع - جمعية الجيل المبدع</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/dd0c5ad80c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/benevole.css">
</head>
<body>
<header class="benevole-header">
    <div class="benevole-header-brand">
        <img src="../public/images/logo_association.png" alt="" class="benevole-header-logo" onerror="this.style.display='none'">
        <h1>فضاء المتطوع</h1>
    </div>

    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <nav class="benevole-nav">
        <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge"></i> لوحتي</a>
        <a href="appels_benevoles.php" class="<?= $currentPage === 'appels_benevoles.php' ? 'active' : '' ?>"><i class="fa-solid fa-bullhorn"></i> نداءات التطوع</a>
        <a href="mes_dons.php" class="<?= $currentPage === 'mes_dons.php' ? 'active' : '' ?>"><i class="fa-solid fa-hand-holding-dollar"></i> تبرعاتي</a>
        <a href="profil.php" class="<?= $currentPage === 'profil.php' ? 'active' : '' ?>"><i class="fa-solid fa-user"></i> حسابي</a>
        <a href="documents.php">📁 ملفاتي</a>
    </nav>

    <div class="benevole-header-user">
        <a href="notifications.php" class="benevole-notif-bell" title="<?= $nbNotifications ?> إشعار يحتاج انتباهكم">
            <i class="fa-solid fa-bell"></i>
            <?php if ($nbNotifications > 0): ?>
                <span class="benevole-notif-badge"><?= $nbNotifications ?></span>
            <?php endif; ?>
        </a>
        <div class="benevole-avatar"><?= htmlspecialchars(mb_substr($_SESSION['benevole_prenom'] ?? '؟', 0, 1)) ?></div>
        <span>مرحبا، <?= htmlspecialchars($_SESSION['benevole_prenom'] ?? '') ?></span>
        <a href="logout.php" class="benevole-logout-btn" title="تسجيل الخروج"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</header>
<main class="benevole-content">
<?php if (basename($_SERVER['PHP_SELF']) !== 'index.php'): ?>
    <button onclick="history.back()" class="benevole-back-btn"><i class="fa-solid fa-arrow-right"></i> رجوع</button>
<?php endif; ?>