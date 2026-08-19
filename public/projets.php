<?php
require_once '../config/db.php';
$pageTitle = 'مشاريعنا';
include '../includes/header_public.php';

$filtreCategorie = $_GET['categorie'] ?? '';

$sql = "SELECT p.*, c.nom AS categorie_nom,
               e.id AS edition_id, e.numero_edition, e.description AS edition_description,
               e.budget_prevu, e.budget_collecte,
               (SELECT url FROM photos_projets WHERE edition_id = e.id ORDER BY date_ajout LIMIT 1) AS photo
        FROM projets p
        LEFT JOIN categories_projets c ON p.categorie_id = c.id
        JOIN projet_editions e ON e.id = (
            SELECT id FROM projet_editions
            WHERE projet_id = p.id AND statut = 'validee'
            ORDER BY numero_edition DESC, date_debut DESC
            LIMIT 1
        )
        WHERE 1=1";
$params = [];
if ($filtreCategorie !== '') {
    $sql .= " AND p.categorie_id = ?";
    $params[] = $filtreCategorie;
}
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projets = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories_projets ORDER BY nom")->fetchAll();
?>

<section class="page-hero orange">
    <div class="container">
        <h1>مشاريعنا</h1>
        <p>اكتشفوا مختلف المشاريع التي تنجزها الجمعية لفائدة الأيتام والأرامل والمحتاجين</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filter-bar">
            <a href="projets.php" class="filter-chip <?= $filtreCategorie === '' ? 'active' : '' ?>">الكل</a>
            <?php foreach ($categories as $c): ?>
                <a href="projets.php?categorie=<?= $c['id'] ?>" class="filter-chip <?= $filtreCategorie == $c['id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($c['nom']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($projets)): ?>
            <p class="badge-empty">لا توجد مشاريع في هذه الفئة حاليا.</p>
        <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projets as $p):
                $pct = $p['budget_prevu'] > 0 ? min(100, round(($p['budget_collecte'] / $p['budget_prevu']) * 100)) : 0;
            ?>
            <div class="project-card">
                <?php if ($p['photo']): ?>
                    <img src="../uploads/<?= htmlspecialchars($p['photo']) ?>" class="project-card-img">
                <?php else: ?>
                    <div class="project-card-img placeholder">لا توجد صورة</div>
                <?php endif; ?>
                <div class="project-card-body">
                    <span class="project-tag"><?= htmlspecialchars($p['categorie_nom'] ?? 'مشروع') ?></span>
                    <h3><?= htmlspecialchars($p['titre']) ?> <small>#<?= $p['numero_edition'] ?></small></h3>
                    <p><?= htmlspecialchars(mb_substr($p['edition_description'], 0, 90)) ?>...</p>
                    <div class="project-progress"><div class="project-progress-fill" style="width:<?= $pct ?>%;"></div></div>
                    <div class="project-progress-label">
                        <?= number_format($p['budget_collecte'],0) ?> / <?= number_format($p['budget_prevu'],0) ?> د.م. (<?= $pct ?>%)
                        <?php if ($p['budget_collecte'] == $p['budget_prevu']): ?>
                            <span class="badge-goal-reached"> الهدف تحقق</span>
                        <?php endif; ?>
                    </div>
                    <a href="projet_detail.php?id=<?= $p['id'] ?>" class="project-card-link">اقرأ المزيد ←</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>