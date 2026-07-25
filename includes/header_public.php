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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/don.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<script src="https://kit.fontawesome.com/dd0c5ad80c.js" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@100..900&family=Betania+Patmos&family=Bungee&family=Dosis:wght@200..800&family=El+Messiri:wght@400..700&family=Gentium+Book+Plus:ital,wght@0,400;0,700;1,400;1,700&family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&family=League+Spartan:wght@100..900&family=Lora:ital,wght@0,400..700;1,400..70OTH=300..700&family=Roboto:ital,wght@0,1００..９００;１,１００..９００&family=Ubuntu:ital,wght@０,３００;０,４００;０,５００;０,７００;１,３００;１,４００;１,５００;１,７００&display=swap" rel="stylesheet">
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
