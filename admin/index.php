<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// --- Chiffres clés ---
$nbProjets = $pdo->query("SELECT COUNT(*) FROM projets")->fetchColumn();
$nbProjetsEnCours = $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'en_cours'")->fetchColumn();
$totalCollecte = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM dons")->fetchColumn();
$nbDonateurs = $pdo->query("SELECT COUNT(DISTINCT email_donateur) FROM dons")->fetchColumn();
$nbBenevolesActifs = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'benevole' AND statut = 'actif'")->fetchColumn();
$nbCandidaturesEnAttente = $pdo->query("SELECT COUNT(*) FROM candidatures_benevoles WHERE statut = 'en_attente'")->fetchColumn();
$nbEditionsEnAttente = $pdo->query("SELECT COUNT(*) FROM projet_editions WHERE statut = 'en_attente_validation'")->fetchColumn();
$nbMessagesNonTraites = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE traite = 0")->fetchColumn();

// --- Dons collectés par mois (6 derniers mois), pour le graphique en courbe ---
$stmt = $pdo->query(
    "SELECT DATE_FORMAT(date_don, '%Y-%m') AS mois, SUM(montant) AS total
     FROM dons
     WHERE date_don >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY mois ORDER BY mois"
);
$donsParMois = $stmt->fetchAll();
$moisLabels = array_column($donsParMois, 'mois');
$moisValeurs = array_map('floatval', array_column($donsParMois, 'total'));

// --- Répartition des projets par catégorie, pour le graphique en secteurs ---
$stmt = $pdo->query(
    "SELECT COALESCE(c.nom, 'بدون تصنيف') AS categorie, COUNT(p.id) AS nb
     FROM projets p LEFT JOIN categories_projets c ON p.categorie_id = c.id
     GROUP BY c.nom"
);
$parCategorie = $stmt->fetchAll();
$categorieLabels = array_column($parCategorie, 'categorie');
$categorieValeurs = array_map('intval', array_column($parCategorie, 'nb'));

include '../includes/header.php';
?>

<h2>لوحة التحكم</h2>
<p class="info-note">نظرة عامة على نشاط الجمعية.</p>

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <i class="fa-solid fa-diagram-project"></i>
        <div><span><?= $nbProjets ?></span><small>مشروع (<?= $nbProjetsEnCours ?> قيد الإنجاز)</small></div>
    </div>
    <div class="admin-stat-card highlight">
        <i class="fa-solid fa-hand-holding-dollar"></i>
        <div><span><?= number_format($totalCollecte, 0) ?> د.م.</span><small>مجموع التبرعات المحصلة</small></div>
    </div>
    <div class="admin-stat-card">
        <i class="fa-solid fa-users"></i>
        <div><span><?= $nbDonateurs ?></span><small>متبرع (متبرعين مختلفين)</small></div>
    </div>
    <div class="admin-stat-card">
        <i class="fa-solid fa-hand"></i>
        <div><span><?= $nbBenevolesActifs ?></span><small>متطوع نشيط</small></div>
    </div>

    <?php if ($nbCandidaturesEnAttente > 0): ?>
    <a href="candidatures.php" class="admin-stat-card alert">
        <i class="fa-solid fa-user-plus"></i>
        <div><span><?= $nbCandidaturesEnAttente ?></span><small>طلب ترشح ينتظر الرد</small></div>
    </a>
    <?php endif; ?>

    <?php if ($nbEditionsEnAttente > 0): ?>
    <a href="projets.php" class="admin-stat-card alert">
        <i class="fa-solid fa-clock"></i>
        <div><span><?= $nbEditionsEnAttente ?></span><small>إصدار ينتظر المصادقة</small></div>
    </a>
    <?php endif; ?>

    <?php if ($nbMessagesNonTraites > 0): ?>
    <a href="messages.php" class="admin-stat-card alert">
        <i class="fa-solid fa-envelope"></i>
        <div><span><?= $nbMessagesNonTraites ?></span><small>رسالة غير معالجة</small></div>
    </a>
    <?php endif; ?>
</div>

<div class="admin-charts-grid">
    <div class="admin-chart-card">
        <h3>التبرعات خلال 6 أشهر الأخيرة</h3>
        <canvas id="chartDons"></canvas>
    </div>
    <div class="admin-chart-card">
        <h3>توزيع المشاريع حسب التصنيف</h3>
        <canvas id="chartCategories"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
new Chart(document.getElementById('chartDons'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($moisLabels) ?>,
        datasets: [{
            label: 'د.م.',
            data: <?= json_encode($moisValeurs) ?>,
            backgroundColor: '#1E3E8C',
            borderRadius: 6
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('chartCategories'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($categorieLabels) ?>,
        datasets: [{
            data: <?= json_encode($categorieValeurs) ?>,
            backgroundColor: ['#1E3E8C', '#E8622C', '#F0B429', '#16a34a', '#6b7280', '#dc2626', '#0ea5e9', '#a855f7']
        }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { font: { family: 'Cairo' } } } } }
});
</script>

<?php include '../includes/footer.php'; ?>