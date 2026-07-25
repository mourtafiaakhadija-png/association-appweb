<?php
session_start();
require_once '../includes/i18n_admin.php';

// Vider et détruire complètement la session
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
