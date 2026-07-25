<?php
session_start();
unset($_SESSION['benevole_id'], $_SESSION['benevole_nom'], $_SESSION['benevole_prenom']);
session_destroy();
header('Location: login.php');
exit;