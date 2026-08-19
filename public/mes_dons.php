<?php
require_once '../config/db.php';
require_once '../includes/mailer.php';
require_once '../config/mail.php';
$pageTitle = 'تبرعاتي';

$dons = [];
$etape = 'formulaire'; // formulaire | email_envoye | resultats | lien_invalide

// --- Étape 1 : demande d'accès (POST) → génère un token et l'envoie par email ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $token = bin2hex(random_bytes(32));
        $expireAt = date('Y-m-d H:i:s', time() + 15 * 60); // valable 15 minutes

        $pdo->prepare(
            "INSERT INTO acces_dons_tokens (email, token, expire_at) VALUES (?, ?, ?)"
        )->execute([$email, $token, $expireAt]);

        $lien = SITE_URL . "/public/mes_dons.php?email=" . urlencode($email) . "&token=" . $token;

        sendMail($email, $email, 'رابط الاطلاع على تبرعاتكم', "
            <p>مرحبا،</p>
            <p>اضغطوا على الرابط التالي للاطلاع على سجل تبرعاتكم (صالح لمدة 15 دقيقة):</p>
            <p><a href='$lien'>$lien</a></p>
            <p>إذا لم تطلبوا هذا الرابط، يمكنكم تجاهل هذه الرسالة بأمان.</p>
        ");
    }
    // Message générique dans tous les cas (email valide ou pas), pour ne jamais révéler
    // si une adresse est associée à des dons ou même si elle existe.
    $etape = 'email_envoye';
}

// --- Étape 2 : vérification du lien reçu par email (GET avec token) ---
elseif (!empty($_GET['email']) && !empty($_GET['token'])) {
    $email = trim($_GET['email']);
    $token = trim($_GET['token']);

    $stmt = $pdo->prepare(
        "SELECT * FROM acces_dons_tokens WHERE email = ? AND token = ? AND utilise = 0 AND expire_at > NOW()"
    );
    $stmt->execute([$email, $token]);
    $jeton = $stmt->fetch();

    if ($jeton) {
        // Jeton valide : à usage unique, on le marque utilisé immédiatement
        $pdo->prepare("UPDATE acces_dons_tokens SET utilise = 1 WHERE id = ?")->execute([$jeton['id']]);

        $stmt = $pdo->prepare(
            "SELECT d.*, p.titre, p.id as projet_id, e.numero_edition, e.description, e.budget_collecte, e.budget_prevu
             FROM dons d 
             JOIN projets p ON d.projet_id = p.id 
             LEFT JOIN projet_editions e ON d.edition_id = e.id
             WHERE d.email_donateur = ? ORDER BY d.date_don DESC"
        );
        $stmt->execute([$email]);
        $dons = $stmt->fetchAll();
        $etape = 'resultats';
    } else {
        $etape = 'lien_invalide';
    }
}

include '../includes/header_public.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>تبرعاتي</h1>
        <p>أدخلوا بريدكم الإلكتروني، وسنرسل لكم رابطا آمنا لعرض سجل تبرعاتكم</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:700px;">

        <?php if ($etape === 'formulaire' || $etape === 'email_envoye' || $etape === 'lien_invalide'): ?>
            <form method="POST" class="search-dons-form">
                <input type="email" name="email" placeholder="بريدكم الإلكتروني" required>
                <button type="submit">إرسال رابط الاطلاع</button>
            </form>
        <?php endif; ?>

        <?php if ($etape === 'email_envoye'): ?>
            <p class="badge-empty" style="margin-top:1.5rem;">
                إذا كان بريدكم الإلكتروني مرتبطا بتبرعات سابقة، ستتوصلون برابط للاطلاع عليها خلال دقائق. تفقدوا صندوق الوارد (ومجلد الرسائل غير المرغوب فيها).
            </p>
        <?php endif; ?>

        <?php if ($etape === 'lien_invalide'): ?>
            <p class="badge-empty" style="margin-top:1.5rem; color:#dc2626;">
                هذا الرابط غير صالح أو منتهي الصلاحية (صالح لمدة 15 دقيقة فقط، ولمرة واحدة). يرجى طلب رابط جديد أعلاه.
            </p>
        <?php endif; ?>

        <?php if ($etape === 'resultats'): ?>
            <?php if (empty($dons)): ?>
                <p class="badge-empty">لا توجد تبرعات مرتبطة بهذا البريد الإلكتروني.</p>
            <?php else: ?>
                <p style="text-align:center; color:var(--ink-soft); margin:1.5rem 0;">
                    مجموع تبرعاتكم: <strong style="color:var(--blue-dark);"><?= number_format(array_sum(array_column($dons, 'montant')), 2) ?> درهم</strong>
                    عبر <?= count($dons) ?> تبرع
                </p>
                <div class="dons-list">
                    <?php foreach ($dons as $d): ?>
                        <div class="don-item">
                            <div>
                                <?php if ($d['numero_edition']): ?>
                                    <a href="projet_detail.php?id=<?= $d['projet_id'] ?>#edition-<?= $d['numero_edition'] ?>" class="don-item-link">
                                        <strong><?= htmlspecialchars($d['titre']) ?> — إصدار #<?= $d['numero_edition'] ?></strong>
                                    </a>
                                <?php else: ?>
                                    <strong><?= htmlspecialchars($d['titre']) ?></strong>
                                <?php endif; ?>
                                <div class="don-item-date"><?= htmlspecialchars($d['date_don']) ?> — <?= htmlspecialchars($d['mode_paiement']) ?></div>

                                <?php if ($d['numero_edition'] && $d['budget_prevu'] > 0):
                                    $pctDon = min(100, round(($d['budget_collecte'] / $d['budget_prevu']) * 100));
                                ?>
                                    <div class="don-tracking-progress">
                                        <div class="project-progress"><div class="project-progress-fill" style="width:<?= $pctDon ?>%;"></div></div>
                                        <small><?= $pctDon ?>% من هدف هذا الإصدار محقق (<?= number_format($d['budget_collecte'],0) ?> / <?= number_format($d['budget_prevu'],0) ?> د.م.)</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="don-item-amount"><?= number_format($d['montant'], 2) ?> د.م.</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php include '../includes/footer_public.php'; ?>