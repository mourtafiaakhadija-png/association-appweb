<?php
session_start();
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../includes/csrf.php';

// Ajout rapide d'une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_categorie'])) {
    verifierJetonCsrf();
    $nom = trim($_POST['new_categorie']);
    if ($nom !== '') {
        $pdo->prepare("INSERT INTO categories_projets (nom) VALUES (?)")->execute([$nom]);
    }
    header('Location: projets.php');
    exit;
}

// Suppression d'un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    verifierJetonCsrf();
    $id = (int) $_POST['delete'];

    $stmtNb = $pdo->prepare("SELECT COUNT(*) FROM dons WHERE projet_id = ?");
    $stmtNb->execute([$id]);
    $nbDons = (int) $stmtNb->fetchColumn();

    if ($nbDons > 0) {
        die("لا يمكن حذف هذا المشروع لأنه يتوفر على $nbDons تبرع (تبرعات) مسجلة. يمكنكم فقط تغيير حالته إلى \"معلق\".");
    }
    $pdo->prepare("DELETE FROM projets WHERE id = ?")->execute([$id]);
    header('Location: projets.php');
    exit;
}

// Filtres
$filtreCategorie = $_GET['categorie'] ?? '';
$filtreStatut = $_GET['statut'] ?? '';

$sql = "SELECT p.*, c.nom AS categorie_nom,
               (SELECT url FROM photos_projets WHERE projet_id = p.id ORDER BY date_ajout LIMIT 1) AS photo_principale,
               (SELECT COALESCE(SUM(budget_prevu), 0) FROM projet_editions WHERE projet_id = p.id) AS total_budget_prevu,
               (SELECT COALESCE(SUM(budget_collecte), 0) FROM projet_editions WHERE projet_id = p.id) AS total_budget_collecte,
               (SELECT COUNT(*) FROM projet_editions WHERE projet_id = p.id) AS nb_editions,
               (SELECT COUNT(*) FROM projet_editions WHERE projet_id = p.id AND statut = 'en_attente_validation') AS nb_en_attente,
               (SELECT COUNT(*) FROM projet_editions WHERE projet_id = p.id AND rapport_statut = 'en_attente') AS nb_rapports_attente,
               (SELECT COUNT(*) FROM mises_a_jour_edition m JOIN projet_editions e2 ON e2.id = m.edition_id WHERE e2.projet_id = p.id AND m.statut = 'en_attente') AS nb_maj_attente
        FROM projets p
        LEFT JOIN categories_projets c ON p.categorie_id = c.id
        WHERE 1=1";
$params = [];

if ($filtreCategorie !== '') {
    $sql .= " AND p.categorie_id = ?";
    $params[] = $filtreCategorie;
}
if ($filtreStatut !== '') {
    $sql .= " AND p.statut = ?";
    $params[] = $filtreStatut;
}
$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projets = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories_projets ORDER BY nom")->fetchAll();

include '../includes/header.php';
?>

<h2>إدارة المشاريع</h2>

<div class="projets-toolbar">
    <a class="btn-add" href="projet_form.php">إضافة مشروع + </a>

    <form method="GET" class="filter-form">
        <select name="categorie" onchange="this.form.submit()">
            <option value="">جميع التصنيفات</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $filtreCategorie == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="statut" onchange="this.form.submit()">
            <option value="">جميع الحالات</option>
            <option value="en_cours" <?= $filtreStatut === 'en_cours' ? 'selected' : '' ?>>مستمر</option>
            <option value="termine" <?= $filtreStatut === 'termine' ? 'selected' : '' ?>>منتهي</option>
            <option value="suspendu" <?= $filtreStatut === 'suspendu' ? 'selected' : '' ?>>معلق</option>
        </select>
    </form>
</div>

<details class="categorie-manager">
    <summary>إدارة التصنيفات(<?= count($categories) ?>)</summary>
    <ul>
        <?php foreach ($categories as $c): ?>
            <li><?= htmlspecialchars($c['nom']) ?></li>
        <?php endforeach; ?>
    </ul>
    <form method="POST" class="inline-form">
        <input type="text" name="new_categorie" placeholder="تصنيف جديد (مثال: كفالة الأيتام)" required>
        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
        <button type="submit">إضافة</button>
    </form>
</details>

<div class="projets-grid">
    <?php foreach ($projets as $p): ?>
        <div class="projet-card">
            <?php if ($p['photo_principale']): ?>
                <img src="../uploads/<?= htmlspecialchars($p['photo_principale']) ?>" class="projet-thumb">
            <?php else: ?>
                <div class="projet-thumb placeholder">لا توجد صورة</div>
            <?php endif; ?>

            <div class="projet-body">
                <span class="badge badge-<?= $p['statut'] ?>"><?= label('statut_projet', $p['statut']) ?></span>
                <?php if ($p['nb_en_attente'] > 0): ?>
                    <span class="badge badge-attente">⏳ <?= $p['nb_en_attente'] ?> إصدار في انتظار المصادقة</span>
                <?php endif; ?>
                <?php if ($p['nb_rapports_attente'] > 0): ?>
                    <a href="rapports.php?projet_id=<?= $p['id'] ?>" class="badge badge-rapport" style="text-decoration:none;">📄 <?= $p['nb_rapports_attente'] ?> تقرير في انتظار المراجعة</a>
                <?php endif; ?>
                <?php if ($p['nb_maj_attente'] > 0): ?>
                    <a href="mises_a_jour.php?projet=<?= $p['id'] ?>" class="badge badge-rapport" style="text-decoration:none;">🔄 <?= $p['nb_maj_attente'] ?> تحديث في انتظار المراجعة</a>
                <?php endif; ?>
                <h3><?= htmlspecialchars($p['titre']) ?></h3>
                <p class="projet-meta"><?= htmlspecialchars($p['categorie_nom'] ?? 'بدون تصنيف') ?> · الفئة المستهدفة : <?= label('cible_type', $p['cible_type']) ?></p>

                <div class="progress-bar">
                    <?php
                        $pct = $p['total_budget_prevu'] > 0 ? min(100, round(($p['total_budget_collecte'] / $p['total_budget_prevu']) * 100)) : 0;
                    ?>
                    <div class="progress-fill" style="width: <?= $pct ?>%;"></div>
                </div>
                <p class="projet-budget"><?= number_format($p['total_budget_collecte'], 0) ?> / <?= number_format($p['total_budget_prevu'], 0) ?> MAD (<?= $pct ?>%) — مجموع <?= $p['nb_editions'] ?> إصدار(ات)</p>

                <div class="projet-actions">
                    <a href="projet_form.php?id=<?= $p['id'] ?>">تعديل</a>
                    <a href="projet_editions.php?projet_id=<?= $p['id'] ?>"><i class="fa-solid fa-folder-open"></i> الإصدارات (<?= $p['nb_editions'] ?>)</a>
                    <a href="projet_historique.php?id=<?= $p['id'] ?>">السجل</a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('حذف هذا المشروع مع كل إصداراته؟');">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="delete" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn-mini btn-reject">حذف</button>
                    </form> 
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($projets)): ?>
        <p class="info-note">لا يوجد أي مشروع حالياً. انقر على "+ مشروع جديد" للبدء</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>