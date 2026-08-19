<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// Accepter un rapport
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_rapport_id'])) {
    verifierJetonCsrf();
    $editionId = (int) $_POST['valider_rapport_id'];
    $pdo->prepare("UPDATE projet_editions SET rapport_statut = 'valide', commentaire_rapport = NULL WHERE id = ?")->execute([$editionId]);
    header('Location: rapports.php?' . http_build_query($_GET));
    exit;
}

// Renvoyer un rapport pour correction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renvoyer_rapport_id'])) {
    verifierJetonCsrf();
    $editionId = (int) $_POST['renvoyer_rapport_id'];
    $commentaire = trim($_POST['commentaire_rapport']);
    $pdo->prepare("UPDATE projet_editions SET rapport_statut = 'a_corriger', commentaire_rapport = ? WHERE id = ?")->execute([$commentaire, $editionId]);
    header('Location: rapports.php?' . http_build_query($_GET));
    exit;
}

// Filtres
$filtreProjet = $_GET['projet'] ?? '';
$filtreStatut = $_GET['statut'] ?? '';

$sql = "SELECT e.id, e.numero_edition, e.fichier_rapport, e.rapport_statut, e.commentaire_rapport,
               p.id AS projet_id, p.titre AS projet_titre
        FROM projet_editions e
        JOIN projets p ON p.id = e.projet_id
        WHERE e.fichier_rapport IS NOT NULL";
$params = [];
if ($filtreProjet !== '') {
    $sql .= " AND p.id = ?";
    $params[] = $filtreProjet;
}
if ($filtreStatut !== '') {
    $sql .= " AND e.rapport_statut = ?";
    $params[] = $filtreStatut;
}
$sql .= " ORDER BY e.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rapports = $stmt->fetchAll();

$projetsListe = $pdo->query("SELECT id, titre FROM projets ORDER BY titre")->fetchAll();

$rapportStatutLabels = [
    'en_attente' => '⏳ في انتظار المراجعة',
    'a_corriger' => '⚠️ يحتاج إلى تصحيح',
    'valide' => '✅ تم القبول',
];

include '../includes/header.php';
?>

<h2>سجل التقارير</h2>
<p class="info-note">جميع التقارير المرسلة من طرف المتطوعين، عبر كل المشاريع والإصدارات، بما فيها القديمة.</p>

<form method="GET" class="filter-form">
    <select name="projet" onchange="this.form.submit()">
        <option value="">جميع المشاريع</option>
        <?php foreach ($projetsListe as $pr): ?>
            <option value="<?= $pr['id'] ?>" <?= $filtreProjet == $pr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pr['titre']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="statut" onchange="this.form.submit()">
        <option value="">جميع الحالات</option>
        <option value="en_attente" <?= $filtreStatut === 'en_attente' ? 'selected' : '' ?>>في انتظار المراجعة</option>
        <option value="a_corriger" <?= $filtreStatut === 'a_corriger' ? 'selected' : '' ?>>يحتاج تصحيح</option>
        <option value="valide" <?= $filtreStatut === 'valide' ? 'selected' : '' ?>>مقبول</option>
    </select>
</form>

<table class="rh-table rapport-table">
    <thead>
        <tr><th>المشروع</th><th>الإصدار</th><th>الملف</th><th>الحالة</th><th>الإجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($rapports as $e): ?>
        <tr>
            <td><a href="projet_editions.php?projet_id=<?= $e['projet_id'] ?>"><?= htmlspecialchars($e['projet_titre']) ?></a></td>
            <td>#<?= $e['numero_edition'] ?></td>
            <td><a href="../uploads/<?= htmlspecialchars($e['fichier_rapport']) ?>" target="_blank">تحميل</a></td>
            <td><?= $rapportStatutLabels[$e['rapport_statut']] ?? $e['rapport_statut'] ?></td>
            <td>
                <div class="rapport-actions">
                    <?php if ($e['rapport_statut'] !== 'valide'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="valider_rapport_id" value="<?= $e['id'] ?>">
                        <button type="submit" class="btn-mini btn-accept">قبول التقرير</button>
                    </form>
                    <?php endif; ?>

                    <a href="#" class="btn-mini btn-reject" onclick="document.getElementById('renvoi-rapport-<?= $e['id'] ?>').style.display='block'; return false;">إعادته للتصحيح</a>

                    <?php if ($e['rapport_statut'] === 'a_corriger' && $e['commentaire_rapport']): ?>
                        <div class="rapport-comment"><?= htmlspecialchars($e['commentaire_rapport']) ?></div>
                    <?php endif; ?>

                    <form method="POST" id="renvoi-rapport-<?= $e['id'] ?>" class="renvoi-form" style="display:none;">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="renvoyer_rapport_id" value="<?= $e['id'] ?>">
                        <textarea name="commentaire_rapport" rows="2" placeholder="ما الذي ينقص التقرير؟" required></textarea>
                        <button type="submit" class="btn-mini">إرسال</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($rapports)): ?>
        <tr><td colspan="5">لا يوجد أي تقرير مرسل حالياً.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>