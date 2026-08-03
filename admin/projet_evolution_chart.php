<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$projetId = (int) ($_GET['projet_id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT id, numero_edition, budget_prevu, budget_collecte 
     FROM projet_editions WHERE projet_id = ? ORDER BY numero_edition ASC"
);
$stmt->execute([$projetId]);
$editions = $stmt->fetchAll();

header('Content-Type: image/png');

// Cas où le projet n'a pas encore d'édition : image vide plutôt qu'une erreur PHP
if (empty($editions)) {
    $img = imagecreatetruecolor(400, 120);
    $bg = imagecolorallocate($img, 244, 245, 247);
    imagefill($img, 0, 0, $bg);
    $gray = imagecolorallocate($img, 150, 150, 150);
    imagestring($img, 3, 100, 55, "No data yet", $gray);
    imagepng($img);
    imagedestroy($img);
    exit;
}

// Nombre de donateurs uniques par édition
$editionIds = array_column($editions, 'id');
$placeholders = implode(',', array_fill(0, count($editionIds), '?'));
$stmtDonateurs = $pdo->prepare(
    "SELECT edition_id, COUNT(DISTINCT email_donateur) AS nb FROM dons WHERE edition_id IN ($placeholders) GROUP BY edition_id"
);
$stmtDonateurs->execute($editionIds);
$donateursParEdition = [];
foreach ($stmtDonateurs->fetchAll() as $row) {
    $donateursParEdition[$row['edition_id']] = (int) $row['nb'];
}

// --- Dimensions et marges ---
$width = 720; $height = 360;
$marginLeft = 65; $marginRight = 70; $marginTop = 45; $marginBottom = 45;
$chartWidth = $width - $marginLeft - $marginRight;
$chartHeight = $height - $marginTop - $marginBottom;

$img = imagecreatetruecolor($width, $height);
imageantialias($img, true);

$white = imagecolorallocate($img, 255, 255, 255);
$gridColor = imagecolorallocate($img, 232, 232, 232);
$axisColor = imagecolorallocate($img, 140, 140, 140);
$blue = imagecolorallocate($img, 30, 62, 140);    // budget collecté
$orange = imagecolorallocate($img, 232, 98, 44);  // nombre de donateurs
$textColor = imagecolorallocate($img, 70, 70, 70);

imagefill($img, 0, 0, $white);

$maxBudget = max(array_merge(array_column($editions, 'budget_prevu'), array_column($editions, 'budget_collecte'), [1]));
$maxDonateurs = max(array_merge(array_values($donateursParEdition), [1]));

$n = count($editions);
$stepX = $n > 1 ? $chartWidth / ($n - 1) : 0;

// Grille + graduations (budget à gauche, donateurs à droite)
for ($i = 0; $i <= 4; $i++) {
    $y = $marginTop + $chartHeight - ($i / 4) * $chartHeight;
    imageline($img, $marginLeft, $y, $width - $marginRight, $y, $gridColor);
    imagestring($img, 2, 5, (int) $y - 6, number_format(round(($i / 4) * $maxBudget), 0), $blue);
    imagestring($img, 2, $width - $marginRight + 8, (int) $y - 6, (string) round(($i / 4) * $maxDonateurs), $orange);
}

// Calcul des points + labels d'édition sur l'axe X
$pointsBudget = [];
$pointsDonateurs = [];
foreach ($editions as $i => $e) {
    $x = $marginLeft + $i * $stepX;
    $pointsBudget[] = [$x, $marginTop + $chartHeight - ($e['budget_collecte'] / $maxBudget) * $chartHeight];
    $nbDon = $donateursParEdition[$e['id']] ?? 0;
    $pointsDonateurs[] = [$x, $marginTop + $chartHeight - ($nbDon / $maxDonateurs) * $chartHeight];
    imagestring($img, 3, (int) $x - 10, $height - $marginBottom + 10, "#" . $e['numero_edition'], $textColor);
}

// Lignes
imagesetthickness($img, 3);
for ($i = 0; $i < $n - 1; $i++) {
    imageline($img, (int) $pointsBudget[$i][0], (int) $pointsBudget[$i][1], (int) $pointsBudget[$i+1][0], (int) $pointsBudget[$i+1][1], $blue);
    imageline($img, (int) $pointsDonateurs[$i][0], (int) $pointsDonateurs[$i][1], (int) $pointsDonateurs[$i+1][0], (int) $pointsDonateurs[$i+1][1], $orange);
}
imagesetthickness($img, 1);

// Points
foreach ($pointsBudget as $p) { imagefilledellipse($img, (int) $p[0], (int) $p[1], 8, 8, $blue); }
foreach ($pointsDonateurs as $p) { imagefilledellipse($img, (int) $p[0], (int) $p[1], 8, 8, $orange); }

// Axes
imageline($img, $marginLeft, $marginTop, $marginLeft, $height - $marginBottom, $axisColor);
imageline($img, $marginLeft, $height - $marginBottom, $width - $marginRight, $height - $marginBottom, $axisColor);

// Légende (texte latin uniquement, cf. explication)
imagefilledrectangle($img, $marginLeft, 12, $marginLeft + 12, 24, $blue);
imagestring($img, 3, $marginLeft + 18, 12, "Budget collecte (MAD)", $textColor);
imagefilledrectangle($img, $marginLeft + 230, 12, $marginLeft + 242, 24, $orange);
imagestring($img, 3, $marginLeft + 248, 12, "Nombre de donateurs", $textColor);

imagepng($img);
imagedestroy($img);