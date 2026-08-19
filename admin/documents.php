<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../includes/upload_helper.php';
require_once '../includes/error_handler.php';

// Upload d'un nouveau document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_projet_id'])) {
    verifierJetonCsrf();
    try {
        $projetIdUpload = (int) $_POST['upload_projet_id'];
        $description = trim($_POST['description'] ?? '');
        $filename = handleGenericFileUpload('fichier');

        if ($filename === null) {
            die("لم يتم اختيار أي ملف.");
        }

        $nomOriginal = $_FILES['fichier']['name'];
        $pdo->prepare(
            "INSERT INTO documents_projets (projet_id, nom_original, url, description, uploaded_by) VALUES (?, ?, ?, ?, ?)"
        )->execute([$projetIdUpload, $nomOriginal, $filename, $description, $_SESSION['user_id']]);

        header('Location: documents.php?projet=' . $projetIdUpload);
        exit;
    } catch (Exception $e) {
        gererErreur($e, "حدث خطأ أثناء رفع الملف.");
    }
}

// Suppression d'un document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_doc_id'])) {
    verifierJetonCsrf();
    $docId = (int) $_POST['delete_doc_id'];
    $pdo->prepare("DELETE FROM documents_projets WHERE id = ?")->execute([$docId]);
    header('Location: documents.php?' . http_build_query($_GET));
    exit;
}

$filtreProjet = $_GET['projet'] ?? '';

$sql = "SELECT d.*, p.titre AS projet_titre, u.prenom, u.nom
        FROM documents_projets d
        JOIN projets p ON p.id = d.projet_id
        JOIN users u ON u.id = d.uploaded_by
        WHERE 1=1";
$params = [];
if ($filtreProjet !== '') {
    $sql .= " AND d.projet_id = ?";
    $params[] = $filtreProjet;
}
$sql .= " ORDER BY d.date_ajout DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll();

$projetsListe = $pdo->query("SELECT id, titre FROM projets ORDER BY titre")->fetchAll();

include '../includes/header.php';
?>

<h2>📁 ملفات المشاريع</h2>
<p class="info-note">مساحة لتخزين أي وثيقة مرتبطة بمشروع (صور، PDF، Word، ZIP) — قوائم الأيتام، دراسات، إلخ.</p>

<form method="GET" class="filter-form">
    <select name="projet" onchange="this.form.submit()">
        <option value="">جميع المشاريع</option>
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
            <option value="">اختر مشروعا</option>
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
    <thead>
        <tr><th>الملف</th><th>المشروع</th><th>الوصف</th><th>أضيف من طرف</th><th>التاريخ</th><th>الإجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($documents as $doc): ?>
        <tr>
            <td><a href="../uploads/<?= htmlspecialchars($doc['url']) ?>" target="_blank">📄 <?= htmlspecialchars($doc['nom_original']) ?></a></td>
            <td><a href="projet_editions.php?projet_id=<?= $doc['projet_id'] ?>"><?= htmlspecialchars($doc['projet_titre']) ?></a></td>
            <td><?= htmlspecialchars($doc['description'] ?: '-') ?></td>
            <td><?= htmlspecialchars($doc['prenom'] . ' ' . $doc['nom']) ?></td>
            <td><?= htmlspecialchars($doc['date_ajout']) ?></td>
            <td>
                <form method="POST" onsubmit="return confirm('حذف هذا الملف نهائيا؟');">
                    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                    <input type="hidden" name="delete_doc_id" value="<?= $doc['id'] ?>">
                    <button type="submit" class="btn-mini btn-reject">حذف</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($documents)): ?>
        <tr><td colspan="6">لا يوجد أي ملف حاليا.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>