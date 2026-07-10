<?php
session_start();

// Vider et détruire complètement la session
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
