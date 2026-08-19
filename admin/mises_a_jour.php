<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../includes/notifications.php';

// Accepter une mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_maj_id'])) {
    verifierJetonCsrf();
    $majId = (int) $_POST['valider_maj_id'];

    $pdo->prepare(
        "UPDATE mises_a_jour_edition SET statut = 'validee', commentaire_admin = NULL, date_validation = NOW() WHERE id = ?"
    )->execute([$majId]);

    // Notifier les donateurs du projet concerné
    $stmt = $pdo->prepare(
        "SELECT p.id AS projet_id, p.titre AS projet_titre
         FROM mises_a_jour_edition m
         JOIN projet_editions e ON e.id = m.edition_id
         JOIN projets p ON p.id = e.projet_id
         WHERE m.id = ?"
    );
    $stmt->execute([$majId]);
    $info = $stmt->fetch();
    if ($info) {
        notifierDonateursProjet($pdo, $info['projet_id'], $info['projet_titre']);
    }

    header('Location: mises_a_jour.php?' . http_build_query($_GET));
    exit;
}

// Renvoyer une mise à jour pour correction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renvoyer_maj_id'])) {
    verifierJetonCsrf();
    $majId = (int) $_POST['renvoyer_maj_id'];
    $commentaire = trim($_POST['commentaire_admin']);
    $pdo->prepare(
        "UPDATE mises_a_jour_edition SET statut = 'a_corriger', commentaire_admin = ? WHERE id = ?"
    )->execute([$commentaire, $majId]);
    header('Location: mises_a_jour.php?' . http_build_query($_GET));
    exit;
}

// Filtres
$filtreProjet = $_GET['projet'] ?? '';
$filtreStatut = $_GET['statut'] ?? '';

$sql = "SELECT m.*, e.numero_edition, p.id AS projet_id, p.titre AS projet_titre
        FROM mises_a_jour_edition m
        JOIN projet_editions e ON e.id = m.edition_id
        JOIN projets p ON p.id = e.projet_id
        WHERE 1=1";
$params = [];
if ($filtreProjet !== '') {
    $sql .= " AND p.id = ?";
    $params[] = $filtreProjet;
}
if ($filtreStatut !== '') {
    $sql .= " AND m.statut = ?";
    $params[] = $filtreStatut;
}
$sql .= " ORDER BY m.date_ajout DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$majs = $stmt->fetchAll();

// Photos rattachées à chaque mise à jour
$photosByMaj = [];
if (!empty($majs)) {
    $ids = array_column($majs, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmtP = $pdo->prepare("SELECT * FROM photos_projets WHERE maj_id IN ($placeholders)");
    $stmtP->execute($ids);
    foreach ($stmtP->fetchAll() as $ph) {
        $photosByMaj[$ph['maj_id']][] = $ph;
    }
}

$projetsListe = $pdo->query("SELECT id, titre FROM projets ORDER BY titre")->fetchAll();

$statutLabels = [
    'en_attente' => '⏳ في انتظار المراجعة',
    'a_corriger' => '⚠️ يحتاج إلى تصحيح',
    'validee' => '✅ تم القبول',
];

include '../includes/header.php';
?>

<h2>سجل التحديثات (Evolution)</h2>
<p class="info-note">كل التحديثات المرسلة من المتطوعين المسؤولين عبر مختلف المشاريع.</p>

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
        <option value="validee" <?= $filtreStatut === 'validee' ? 'selected' : '' ?>>مقبول</option>
    </select>
</form>

<table class="rh-table rapport-table">
    <thead>
        <tr><th>المشروع</th><th>الإصدار</th><th>المحتوى</th><th>الحالة</th><th>الإجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($majs as $m): ?>
        <tr>
            <td><a href="projet_editions.php?projet_id=<?= $m['projet_id'] ?>"><?= htmlspecialchars($m['projet_titre']) ?></a></td>
            <td>#<?= $m['numero_edition'] ?></td>
            <td>
                <p style="max-width:320px; white-space:pre-wrap;"><?= htmlspecialchars(mb_substr($m['contenu'], 0, 200)) ?><?= mb_strlen($m['contenu']) > 200 ? '...' : '' ?></p>
                <?php if (!empty($photosByMaj[$m['id']])): ?>
                    <p style="font-size:0.85rem; color:#666;">📷 <?= count($photosByMaj[$m['id']]) ?> صورة —
                        <?php foreach ($photosByMaj[$m['id']] as $ph): ?>
                            <a href="../uploads/<?= htmlspecialchars($ph['url']) ?>" target="_blank">عرض</a>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>
            </td>
            <td><?= $statutLabels[$m['statut']] ?? $m['statut'] ?></td>
            <td>
                <div class="rapport-actions">
                    <?php if ($m['statut'] !== 'validee'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="valider_maj_id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn-mini btn-accept">قبول ونشر</button>
                    </form>
                    <?php endif; ?>

                    <a href="#" class="btn-mini btn-reject" onclick="document.getElementById('renvoi-maj-<?= $m['id'] ?>').style.display='block'; return false;">إعادته للتصحيح</a>

                    <?php if ($m['statut'] === 'a_corriger' && $m['commentaire_admin']): ?>
                        <div class="rapport-comment"><?= htmlspecialchars($m['commentaire_admin']) ?></div>
                    <?php endif; ?>

                    <form method="POST" id="renvoi-maj-<?= $m['id'] ?>" class="renvoi-form" style="display:none;">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="renvoyer_maj_id" value="<?= $m['id'] ?>">
                        <textarea name="commentaire_admin" rows="2" placeholder="ما الذي يجب تصحيحه؟" required></textarea>
                        <button type="submit" class="btn-mini">إرسال</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($majs)): ?>
        <tr><td colspan="5">لا يوجد أي تحديث حالياً.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>