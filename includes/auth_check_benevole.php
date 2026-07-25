<?php


if (!isset($_SESSION['benevole_id'])) {
    header('Location: login.php');
    exit;
}