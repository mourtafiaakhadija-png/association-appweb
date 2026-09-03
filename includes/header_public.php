<?php
// public/includes/header_public.php
// Header commun à toutes les pages publiques
if (!isset($pdo)) { require_once __DIR__ . '/../../config/db.php'; }
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>جمعية الجيل المبدع</title>
<meta name="description" content="جمعية الجيل المبدع - جمعية خيرية بتارودانت، نعمل منذ 2020 على كفالة الأيتام والتضامن الاجتماعي. تبرع الآن وساهم معنا.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/don.css">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<script src="https://kit.fontawesome.com/dd0c5ad80c.js" crossorigin="anonymous"></script>

</head>
<body>

<header class="site-header" id="siteHeader">
    <div class="container header-inner">
        <a href="index.php" class="brand">
            <img src="images/logo_association.png" alt="جمعية الجيل المبدع" class="brand-logo" onerror="this.style.display='none'">
            <span class="brand-text">جمعية الجيل المبدع</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="القائمة">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="mainNav">
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">الرئيسية</a>
            <a href="about.php" class="<?= $currentPage === 'about.php' ? 'active' : '' ?>">من نحن</a>
            <a href="projets.php" class="<?= in_array($currentPage, ['projets.php','projet_detail.php']) ? 'active' : '' ?>">مشاريعنا</a>
            <a href="galerie.php" class="<?= $currentPage === 'galerie.php' ? 'active' : '' ?>">معرض الصور</a>
            <a href="contact.php" class="<?= $currentPage === 'contact.php' ? 'active' : '' ?>">اتصل بنا</a>
        </nav>
    </div>
</header>

<main>
