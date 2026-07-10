<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/db.php';
include '../includes/header.php';
?>

<h2>Tableau de bord</h2>
<p>Bienvenue dans l'espace d'administration. Vous êtes bien connecté(e).</p>

<?php include '../includes/footer.php'; ?>
