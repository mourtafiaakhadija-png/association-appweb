<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جمعية الجيل المبدع - فضاء الإدارة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/dd0c5ad80c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/rh.css">
    <link rel="stylesheet" href="css/projets.css">
</head>
<body>
<header class="admin-header">
    <div class="admin-header-brand">
        <img src="../public/images/logo_association.png" alt="" class="admin-header-logo" onerror="this.style.display='none'">
        <h1>فضاء الإدارة</h1>
    </div>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
        <nav class="admin-nav">
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge"></i> لوحة التحكم</a>
            <a href="rh.php" class="<?= $currentPage === 'rh.php' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> الموارد البشرية</a>
            <a href="projets.php" class="<?= $currentPage === 'projets.php' ? 'active' : '' ?>"><i class="fa-solid fa-diagram-project"></i> المشاريع</a>
            <a href="rapports.php" class="<?= $currentPage === 'rapports.php' ? 'active' : '' ?>"><i class="fa-solid fa-file-lines"></i> التقارير</a>
            <a href="mises_a_jour.php" class="<?= $currentPage === 'mises_a_jour.php' ? 'active' : '' ?>"><i class="fa-solid fa-arrows-rotate"></i> التحديثات</a>
            <a href="dons.php" class="<?= $currentPage === 'dons.php' ? 'active' : '' ?>"><i class="fa-solid fa-hand-holding-dollar"></i> التبرعات</a>
            <a href="messages.php" class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>"><i class="fa-solid fa-envelope"></i> الرسائل</a>
            <a href="candidatures.php" class="<?= $currentPage === 'candidatures.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-plus"></i> طلبات الترشح</a>
            <a href="comites.php" class="<?= $currentPage === 'comites.php' ? 'active' : '' ?>"><i class="fa-solid fa-people-group"></i> اللجان</a>
            <a href="documents.php" class="<?= $currentPage === 'documents.php' ? 'active' : '' ?>"><i class="fa-solid fa-folder-open"></i> الملفات</a>
        </nav>


        <div class="admin-header-user">
            <div class="admin-avatar"><?= htmlspecialchars(mb_substr($_SESSION['user_prenom'] ?? '؟', 0, 1)) ?></div>
            <span>مرحبا، <?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?></span>
            <a href="logout.php" class="admin-logout-btn" title="تسجيل الخروج"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    <?php endif; ?>
</header>
<main class="admin-content">
<?php if (basename($_SERVER['PHP_SELF']) !== 'index.php'): ?>
    <button onclick="history.back()" class="admin-back-btn"><i class="fa-solid fa-arrow-right"></i> رجوع</button>
<?php endif; ?>