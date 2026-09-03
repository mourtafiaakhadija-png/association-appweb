<?php
require_once 'config/db.php';
require_once 'includes/csrf.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'تبرع الآن';

$projetPreselectionne = (int) ($_GET['projet_id'] ?? 0);

// On ne propose que les projets qui ont au moins une édition validée,
// et pour chacun on récupère automatiquement SON ÉDITION ACTUELLE
// (la plus récente validée : numéro d'édition le plus élevé, dates les plus récentes)
$projets = $pdo->query("
    SELECT p.id, p.titre, e.id AS edition_id, e.numero_edition, e.budget_prevu, e.budget_collecte
    FROM projets p
    JOIN projet_editions e ON e.id = (
        SELECT id FROM projet_editions
        WHERE projet_id = p.id AND statut = 'validee'
        ORDER BY numero_edition DESC, date_debut DESC
        LIMIT 1
    )
    WHERE p.statut != 'termine'
    ORDER BY p.created_at DESC
")->fetchAll();

if (empty($projets)) {
    $projets = $pdo->query("
        SELECT p.id, p.titre, e.id AS edition_id, e.numero_edition, e.budget_prevu, e.budget_collecte
        FROM projets p
        JOIN projet_editions e ON e.id = (
            SELECT id FROM projet_editions
            WHERE projet_id = p.id AND statut = 'validee'
            ORDER BY numero_edition DESC, date_debut DESC
            LIMIT 1
        )
        ORDER BY p.created_at DESC
    ")->fetchAll();
}

include 'includes/header_public.php';
?>

<section class="page-hero orange">
    <div class="container">
        <h1>تبرع الآن 🤲</h1>
        <p>تبرعكم نور يمحو جزءا من تعب الأيتام والمحتاجين</p>
    </div>
</section>

<section class="section">
    <div class="container don-layout">

        <div class="don-form-card">
            <h2>استمارة التبرع</h2>
            <form method="POST" action="don_process.php" class="don-form">
                <label>المشروع الذي تريد دعمه</label>
                <select name="projet_id" required>
                    <option value="">-- اختر مشروعا --</option>
                    <?php foreach ($projets as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $projetPreselectionne == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['titre']) ?> — الإصدار #<?= $p['numero_edition'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>الاسم الكامل</label>
                <input type="text" name="nom_donateur" required placeholder="اسمك الكامل">

                <label>البريد الإلكتروني</label>
                <input type="email" name="email_donateur" required placeholder="بريدك الإلكتروني (لتتبع تبرعاتك واستلام الوصل)">

                <label>المبلغ (درهم)</label>
                <input type="number" name="montant" min="10" step="1" required placeholder="مثال: 200">

                <label>طريقة الأداء</label>
                <select name="mode_paiement" required>
                    <option value="virement_bancaire">تحويل بنكي</option>
                    <option value="especes">نقدا (في مقر الجمعية)</option>
                    <option value="cheque">شيك</option>
                </select>
                <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                <button type="submit">تأكيد التبرع</button>
                <p class="form-note">بعد التأكيد، سنسجل تبرعكم ونرسل لكم تفاصيل الحساب البنكي عبر البريد الإلكتروني لإتمام التحويل.</p>
            </form>
        </div>

        <div class="don-info-card">
            <h3><i class="fa-brands fa-cc-discover"></i> معلومات الحساب البنكي</h3>
            <p>يمكنكم إتمام التبرع مباشرة عبر التحويل البنكي إلى:</p>
            <div class="rib-box">
                <span>RIB</span>
                <strong>350810000000110741288</strong>
            </div>
            <p class="don-info-note">جمعية الجيل المبدع — تارودانت</p>

            <hr>

            <h3><i class="fa-solid fa-chart-line"></i> تتبع تبرعاتي</h3>
            <p>هل سبق لكم التبرع؟ يمكنكم الاطلاع على سجل تبرعاتكم.</p>
            <a href="mes_dons.php" class="btn-outline-blue">عرض تبرعاتي</a>
        </div>

    </div>
</section>

<?php include 'includes/footer_public.php'; ?>