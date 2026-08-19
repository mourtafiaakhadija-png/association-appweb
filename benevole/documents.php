<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';
require_once '../includes/upload_helper.php';
require_once '../includes/error_handler.php';

$benevoleId = $_SESSION['benevole_id'];

// Projets dont ce bénévole est responsable
$projetsListe = $pdo->prepare("SELECT id, titre FROM projets WHERE responsable_id = ? ORDER BY titre");
$projetsListe->execute([$benevoleId]);
$projetsListe = $projetsListe->fetchAll();
$projetIdsAutorises = array_column($projetsListe, 'id');

if (empty($projetIdsAutorises)) {
    include '../includes/header_benevole.php';
    echo '<p class="badge-empty">أنتم لستم مسؤولين عن أي مشروع حاليا.</p>';
    include '../includes/footer_benevole.php';
    exit;
}

// Upload d'un nouveau document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_projet_id'])) {
    verifierJetonCsrf();
    $projetIdUpload = (int) $_POST['upload_projet_id'];

    if (!in_array($projetIdUpload, $projetIdsAutorises)) {
        die("غير مسموح لك بالإضافة لهذا المشروع.");
    }

    try {
        $description = trim($_POST['description'] ?? '');
        $filename = handleGenericFileUpload('fichier');
        if ($filename === null) die("لم يتم اختيار أي ملف.");

        $nomOriginal = $_FILES['fichier']['name'];
        $pdo->prepare(
            "INSERT INTO documents_projets (projet_id, nom_original, url, description, uploaded_by) VALUES (?, ?, ?, ?, ?)"
        )->execute([$projetIdUpload, $nomOriginal, $filename, $description, $benevoleId]);

        header('Location: documents.php?projet=' . $projetIdUpload);
        exit;
    } catch (Exception $e) {
        gererErreur($e, "حدث خطأ أثناء رفع الملف.");
    }
}

// Suppression (uniquement ses propres projets)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_doc_id'])) {
    verifierJetonCsrf();
    $docId = (int) $_POST['delete_doc_id'];
    $stmtV = $pdo->prepare("SELECT projet_id FROM documents_projets WHERE id = ?");
    $stmtV->execute([$docId]);
    $projetIdDoc = $stmtV->fetchColumn();
    if (in_array($projetIdDoc, $projetIdsAutorises)) {
        $pdo->prepare("DELETE FROM documents_projets WHERE id = ?")->execute([$docId]);
    }
    header('Location: documents.php?' . http_build_query($_GET));
    exit;
}

$filtreProjet = $_GET['projet'] ?? '';
$placeholders = implode(',', array_fill(0, count($projetIdsAutorises), '?'));

$sql = "SELECT d.*, p.titre AS projet_titre, u.prenom, u.nom
        FROM documents_projets d
        JOIN projets p ON p.id = d.projet_id
        JOIN users u ON u.id = d.uploaded_by
        WHERE d.projet_id IN ($placeholders)";
$params = $projetIdsAutorises;
if ($filtreProjet !== '' && in_array((int) $filtreProjet, $projetIdsAutorises)) {
    $sql .= " AND d.projet_id = ?";
    $params[] = $filtreProjet;
}
$sql .= " ORDER BY d.date_ajout DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll();

include '../includes/header_benevole.php';
?>

<h2>📁 ملفات مشاريعي</h2>
<p class="info-note">مساحة لتخزين أي وثيقة مرتبطة بمشاريعكم (صور، PDF، Word، ZIP).</p>

<form method="GET" class="filter-form">
    <select name="projet" onchange="this.form.submit()">
        <option value="">جميع مشاريعي</option>
        <?php foreach ($projetsListe as $pr): ?>
            <option value="<?= $pr['id'] ?>" <?= $filtreProjet == $pr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pr['titre']) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<div class="projet-form" style="max-width:600px; margin:1.5rem 0;">
    <h3>➕ رفع ملف جديد</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
        <label>المشروع</label>
        <select name="upload_projet_id" required>
            <?php foreach ($projetsListe as $pr): ?>
                <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['titre']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>وصف الملف (اختياري)</label>
        <input type="text" name="description" placeholder="مثال: لائحة الأيتام المستفيدين 2026">
        <label>الملف (PDF, Word, ZIP, صورة — حتى 20 ميغا)</label>
        <input type="file" name="fichier" accept=".pdf,.doc,.docx,.zip,image/*" required>
        <button type="submit" style="margin-top:1rem;">رفع الملف</button>
    </form>
</div>

<table class="rh-table">
    <thead><tr><th>الملف</th><th>المشروع</th><th>الوصف</th><th>التاريخ</th><th>الإجراءات</th></tr></thead>
    <tbody>
    <?php foreach ($documents as $doc): ?>
        <tr>
            <td><a href="../uploads/<?= htmlspecialchars($doc['url']) ?>" target="_blank">📄 <?= htmlspecialchars($doc['nom_original']) ?></a></td>
            <td><?= htmlspecialchars($doc['projet_titre']) ?></td>
            <td><?= htmlspecialchars($doc['description'] ?: '-') ?></td>
            <td><?= htmlspecialchars($doc['date_ajout']) ?></td>
            <td>
                <form method="POST" onsubmit="return confirm('حذف هذا الملف؟');">
                    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                    <input type="hidden" name="delete_doc_id" value="<?= $doc['id'] ?>">
                    <button type="submit" class="btn-mini btn-reject">حذف</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($documents)): ?><tr><td colspan="5">لا يوجد أي ملف حاليا.</td></tr><?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer_benevole.php'; ?>