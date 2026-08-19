<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../includes/mailer.php';
require_once '../includes/error_handler.php';

// --- Ajout manuel d'un don (cash, chèque, virement effectué hors site) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajout_manuel'])) {
    verifierJetonCsrf();

    $projetId = (int) $_POST['projet_id_don'];
    $nomDonateur = trim($_POST['nom_donateur']);
    $emailDonateur = trim($_POST['email_donateur']);
    $montant = (float) $_POST['montant'];
    $modePaiement = $_POST['mode_paiement'];

    $erreurs = [];
    if ($projetId <= 0) $erreurs[] = "اختر مشروعا";
    if ($nomDonateur === '') $erreurs[] = "الاسم مطلوب";
    if ($emailDonateur !== '' && !filter_var($emailDonateur, FILTER_VALIDATE_EMAIL)) $erreurs[] = "البريد الإلكتروني غير صالح";
    if ($montant <= 0) $erreurs[] = "المبلغ يجب أن يكون أكبر من صفر";

    // L'édition actuelle du projet (même logique que le site public)
    $stmtEdition = $pdo->prepare(
        "SELECT e.id, p.titre FROM projet_editions e JOIN projets p ON p.id = e.projet_id
        WHERE e.projet_id = ? AND e.statut = 'validee'
        ORDER BY e.numero_edition DESC, e.date_debut DESC LIMIT 1"
    );
    $stmtEdition->execute([$projetId]);
    $editionCible = $stmtEdition->fetch();
    if (!$editionCible) $erreurs[] = "لا يوجد إصدار منشور حاليا لهذا المشروع";

    if (empty($erreurs)) {
        try {
            $pdo->beginTransaction();

            $pdo->prepare(
                "INSERT INTO dons (projet_id, edition_id, nom_donateur, email_donateur, montant, mode_paiement) VALUES (?, ?, ?, ?, ?, ?)"
            )->execute([$projetId, $editionCible['id'], $nomDonateur, $emailDonateur ?: null, $montant, $modePaiement]);
            $donId = $pdo->lastInsertId();

            $pdo->prepare(
                "UPDATE projet_editions SET budget_collecte = budget_collecte + ? WHERE id = ?"
            )->execute([$montant, $editionCible['id']]);

            $pdo->prepare(
                "INSERT INTO historique_projets (projet_id, description_action) VALUES (?, ?)"
            )->execute([$projetId, "تبرع يدوي بمبلغ " . number_format($montant, 2) . " درهم من " . $nomDonateur . " (سُجّل من طرف الإدارة)"]);

            $pdo->commit();

            if ($emailDonateur !== '') {
                sendMail($emailDonateur, $nomDonateur, 'تأكيد تسجيل تبرعكم - جمعية الجيل المبدع', "
                    <p>عزيزي/عزيزتي " . htmlspecialchars($nomDonateur) . "،</p>
                    <p>نؤكد تسجيل تبرعكم بمبلغ " . number_format($montant, 2) . " درهم. شكرا جزيلا على دعمكم ❤️</p>
                ");
            }

            header('Location: dons.php?projet_id=' . $projetId);
            exit;
        } catch (Exception $e) {
            gererErreur($e, "حدث خطأ أثناء تسجيل التبرع.");
        }
    }
}

$filtreProjet = $_GET['projet_id'] ?? '';

$sql = "SELECT d.*, p.titre FROM dons d JOIN projets p ON d.projet_id = p.id WHERE 1=1";
$params = [];
if ($filtreProjet !== '') {
    $sql .= " AND d.projet_id = ?";
    $params[] = $filtreProjet;
}
$sql .= " ORDER BY d.date_don DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dons = $stmt->fetchAll();

$totalGeneral = array_sum(array_column($dons, 'montant'));
$projets = $pdo->query("SELECT id, titre FROM projets ORDER BY titre")->fetchAll();

include '../includes/header.php';
?>

<h2>إدارة التبرعات</h2>

<?php if (!empty($erreurs)): ?>
    <?php foreach ($erreurs as $err): ?>
        <p class="error"><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<div class="projets-toolbar">
    <p><strong><?= count($dons) ?></strong>التبرعات — الإجمالي:<strong><?= number_format($totalGeneral, 2) ?> MAD</strong></p>

    <form method="GET" class="filter-form">
        <select name="projet_id" onchange="this.form.submit()">
            <option value="">جميع المشاريع</option>
            <?php foreach ($projets as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $filtreProjet == $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['titre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<details class="projet-form" style="max-width:600px; margin-bottom:1.5rem;">
    <summary style="cursor:pointer; font-weight:bold;">➕ تسجيل تبرع يدوي (نقدا، شيك، تحويل خارج الموقع)</summary>
    <form method="POST" style="margin-top:1rem;">
        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
        <input type="hidden" name="ajout_manuel" value="1">

        <label>المشروع</label>
        <select name="projet_id_don" required>
            <option value="">اختر مشروعا</option>
            <?php foreach ($projets as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>اسم المتبرع</label>
        <input type="text" name="nom_donateur" required>

        <label>البريد الإلكتروني (اختياري — إذا أضفتموه، سيتوصل المتبرع بتأكيد وبإشعارات مستجدات المشروع)</label>
        <input type="email" name="email_donateur">

        <label>المبلغ (درهم)</label>
        <input type="number" step="0.01" name="montant" required>

        <label>طريقة الأداء</label>
        <select name="mode_paiement" required>
            <option value="especes">نقدا</option>
            <option value="cheque">شيك</option>
            <option value="virement_bancaire">تحويل بنكي</option>
        </select>

        <button type="submit" style="margin-top:1rem;">تسجيل التبرع</button>
    </form>
</details>

<table class="rh-table">
    <thead>
        <tr><th>التاريخ</th><th>المتبرع</th><th>البريد الإلكتروني</th><th>المشروع</th><th>المبلغ</th><th>طريقة الدفع</th></tr>
    </thead>
    <tbody>
    <?php foreach ($dons as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['date_don']) ?></td>
            <td><?= htmlspecialchars($d['nom_donateur']) ?></td>
            <td><?= htmlspecialchars($d['email_donateur'] ?? '-') ?></td>
            <td><?= htmlspecialchars($d['titre']) ?></td>
            <td><?= number_format($d['montant'], 2) ?> MAD</td>
            <td><?= htmlspecialchars($d['mode_paiement'] ?? '-') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($dons)): ?>
        <tr><td colspan="6">(لا يوجد أي تبرع حالياً)</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>