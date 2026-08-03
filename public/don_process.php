<?php
require_once '../config/db.php';
require_once '../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: don.php');
    exit;
}

$projetId = (int) ($_POST['projet_id'] ?? 0);
$nomDonateur = trim($_POST['nom_donateur'] ?? '');
$emailDonateur = trim($_POST['email_donateur'] ?? '');
$montant = (float) ($_POST['montant'] ?? 0);
$modePaiement = $_POST['mode_paiement'] ?? 'virement_bancaire';

$modeLabels = [
    'virement_bancaire' => 'تحويل بنكي',
    'especes' => 'نقدا',
    'cheque' => 'شيك',
];

$errors = [];
if ($projetId <= 0) $errors[] = "مشروع غير صالح";
if ($nomDonateur === '') $errors[] = "الاسم مطلوب";
if (!filter_var($emailDonateur, FILTER_VALIDATE_EMAIL)) $errors[] = "بريد إلكتروني غير صالح";
if ($montant < 10) $errors[] = "المبلغ يجب أن يكون 10 درهم على الأقل";

$stmt = $pdo->prepare("SELECT * FROM projets WHERE id = ?");
$stmt->execute([$projetId]);
$projet = $stmt->fetch();
if (!$projet) $errors[] = "المشروع غير موجود";

// On ne fait JAMAIS confiance à un edition_id envoyé par le formulaire (il n'y en a d'ailleurs pas) :
// on recalcule nous-mêmes, côté serveur, quelle est l'édition actuelle de ce projet.
$stmtEdition = $pdo->prepare(
    "SELECT id FROM projet_editions 
     WHERE projet_id = ? AND statut = 'validee' 
     ORDER BY numero_edition DESC, date_debut DESC 
     LIMIT 1"
);
$stmtEdition->execute([$projetId]);
$editionId = $stmtEdition->fetchColumn();

if (!$editionId) $errors[] = "لا يوجد إصدار متاح حاليا لهذا المشروع";

if (!empty($errors)) {
    header('Location: don.php?projet_id=' . $projetId . '&error=1');
    exit;
}

try {
    $pdo->beginTransaction();

    $pdo->prepare(
        "INSERT INTO dons (projet_id, edition_id, nom_donateur, email_donateur, montant, mode_paiement) VALUES (?, ?, ?, ?, ?, ?)"
    )->execute([$projetId, $editionId, $nomDonateur, $emailDonateur, $montant, $modePaiement]);
    $donId = $pdo->lastInsertId();

    // Le budget collecté se met à jour sur L'ÉDITION précise, plus sur le projet global
    $pdo->prepare(
        "UPDATE projet_editions SET budget_collecte = budget_collecte + ? WHERE id = ?"
    )->execute([$montant, $editionId]);

    $pdo->prepare(
        "INSERT INTO historique_projets (projet_id, description_action) VALUES (?, ?)"
    )->execute([$projetId, "تبرع جديد بمبلغ " . number_format($montant, 2) . " درهم من " . $nomDonateur]);

    $pdo->commit();

    $bodyHtml = "
        <div dir='rtl' style='font-family:Tajawal,Arial,sans-serif; max-width:600px; margin:0 auto;'>
            <h2 style='color:#1E3E8C;'>شكرا لكم على تبرعكم </h2>
            <p>عزيزي/عزيزتي " . htmlspecialchars($nomDonateur) . "،</p>
            <p>تلقينا نيتكم للتبرع بالتفاصيل التالية:</p>
            <table style='width:100%; border-collapse:collapse; margin:1rem 0;'>
                <tr><td style='padding:8px; border-bottom:1px solid #eee;'><strong>المشروع</strong></td><td style='padding:8px; border-bottom:1px solid #eee;'>" . htmlspecialchars($projet['titre']) . "</td></tr>
                <tr><td style='padding:8px; border-bottom:1px solid #eee;'><strong>المبلغ</strong></td><td style='padding:8px; border-bottom:1px solid #eee;'>" . number_format($montant, 2) . " درهم</td></tr>
                <tr><td style='padding:8px; border-bottom:1px solid #eee;'><strong>طريقة الأداء</strong></td><td style='padding:8px; border-bottom:1px solid #eee;'>" . htmlspecialchars($modeLabels[$modePaiement] ?? $modePaiement) . "</td></tr>
                <tr><td style='padding:8px;'><strong>رقم التبرع</strong></td><td style='padding:8px;'>#" . $donId . "</td></tr>
            </table>
            <p>لإتمام التحويل البنكي، يرجى استعمال المعلومات التالية:</p>
            <div style='background:#f4f5f7; padding:12px 16px; border-radius:8px; margin:1rem 0;'>
                <strong>RIB:</strong> 350810000000110741288<br>
                <strong>المستفيد:</strong> جمعية الجيل المبدع
            </div>
            <p>بارك الله فيكم وجزاكم خيرا على تضامنكم.</p>
            <p style='color:#888; font-size:0.85rem; margin-top:2rem;'>جمعية الجيل المبدع — تارودانت</p>
        </div>
    ";
    sendMail($emailDonateur, $nomDonateur, 'تأكيد تبرعكم - جمعية الجيل المبدع', $bodyHtml);

    header('Location: merci.php?don_id=' . $donId);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("حدث خطأ أثناء تسجيل التبرع. يرجى المحاولة مرة أخرى.");
}