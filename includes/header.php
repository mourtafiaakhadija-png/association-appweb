<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Association de Génération Créative - Espace Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/rh.css">
    <link rel="stylesheet" href="css/projets.css">
</head>
<body>
<header class="admin-header">
    <h1>Espace Administration</h1>
    <?php if (isset($_SESSION['user_id'])): ?>
        <nav>
            <a href="index.php">Tableau de bord</a>
            &nbsp;|&nbsp;
            <a href="rh.php">RH</a>
            &nbsp;|&nbsp;
            <a href="projets.php">Projets</a>
            &nbsp;|&nbsp;
            <span>Bonjour, <?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?></span>
            &nbsp;|&nbsp;
            <a href="logout.php">Se déconnecter</a>
        </nav>
    <?php endif; ?>
</header>
<main class="admin-content">
