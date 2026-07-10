<?php
require_once '../config/db.php';
$pageTitle = 'الرئيسية';
include '../includes/header_public.php';

// Projets à la une : les 3 derniers projets en cours ou terminés avec au moins une photo
$stmt = $pdo->query(
    "SELECT p.*, c.nom AS categorie_nom,
            (SELECT url FROM photos_projets WHERE projet_id = p.id ORDER BY date_ajout LIMIT 1) AS photo
     FROM projets p LEFT JOIN categories_projets c ON p.categorie_id = c.id
     ORDER BY p.created_at DESC LIMIT 3"
);
$projetsAlaUne = $stmt->fetchAll();

$nbProjets = $pdo->query("SELECT COUNT(*) FROM projets")->fetchColumn();
?>

<section class="hero">
    <div class="container hero-inner">
        <div>
            <span class="hero-eyebrow">جمعية الجيل المبدع — تارودانت</span>
            <h1>وتطيب الحياة ... بروح العطاء</h1>
            <p class="lead">نعمل منذ 2020 على مساعدة الأرامل والأيتام، وتوفير الرعاية المادية والصحية والتعليمية والتربوية لأسرة اليتيم عن قرب، وسط أسرته وبيئته.</p>
            <div class="hero-actions">
                <a href="don.php" class="btn-white">تبرع الآن</a>
                <a href="projets.php" class="btn-outline-white">اكتشف مشاريعنا</a>
            </div>
        </div>
        <img src="images/hero_placeholder.jpg" alt="جمعية الجيل المبدع" class="hero-image" onerror="this.style.display='none'">
    </div>

    <svg class="torn-edge" viewBox="0 0 1200 60" preserveAspectRatio="none">
        <path d="M0,20 L40,35 L90,10 L150,30 L210,5 L270,25 L330,8 L400,32 L460,12 L520,28 L580,6 L650,30 L710,10 L780,26 L840,4 L900,28 L960,10 L1020,30 L1080,8 L1140,25 L1200,15 L1200,60 L0,60 Z"></path>
    </svg>
</section>

<section class="stats-bar">
    <div class="container stats-grid">
        <div class="stat-item"><div class="stat-number">120+</div><div class="stat-label">يتيم مكفول</div></div>
        <div class="stat-item"><div class="stat-number">580</div><div class="stat-label">قفة خير وُزعت</div></div>
        <div class="stat-item"><div class="stat-number">700+</div><div class="stat-label">إفطار صائم</div></div>
        <div class="stat-item"><div class="stat-number">200+</div><div class="stat-label">متبرع بالدم</div></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">مشاريعنا</span>
            <h2>أحدث المشاريع</h2>
        </div>

        <?php if (empty($projetsAlaUne)): ?>
            <p class="badge-empty">لا توجد مشاريع لعرضها حاليا.</p>
        <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projetsAlaUne as $p):
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
                    <h3><?= htmlspecialchars($p['titre']) ?></h3>
                    <p><?= htmlspecialchars(mb_substr($p['description'], 0, 90)) ?>...</p>
                    <div class="project-progress"><div class="project-progress-fill" style="width:<?= $pct ?>%;"></div></div>
                    <div class="project-progress-label"><?= number_format($p['budget_collecte'],0) ?> / <?= number_format($p['budget_prevu'],0) ?> د.م. (<?= $pct ?>%)</div>
                    <a href="projet_detail.php?id=<?= $p['id'] ?>" class="project-card-link">اقرأ المزيد ←</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">رسالتنا</span>
            <h2>لماذا جمعية الجيل المبدع</h2>
        </div>
        <div class="values-grid">
            <div class="value-card">
                <h3>🕊️ كفالة اليتيم</h3>
                <p>نضع رعاية الأيتام على رأس أولوياتنا، عبر تقديم مجموعة من الخدمات الموسمية والطموح لكفالة اليتيم في أسرته من خلال مشروع "رافق نبيك بكفالة اليتيم".</p>
            </div>
            <div class="value-card">
                <h3>📖 الاهتمام بالقرآن الكريم</h3>
                <p>نهتم بالكتاتيب القرآنية بإقليم تارودانت، ونسعى إلى بناء كتاب قرآني يضم جميع المرافق الضرورية للطلبة والطالبات.</p>
            </div>
            <div class="value-card">
                <h3>🤝 التضامن الاجتماعي</h3>
                <p>من سقيا الخير إلى قوافل التضامن الشتوية، نعمل على مد يد العون للمناطق الجبلية والمحتاجة للماء الصالح للشرب والدفء.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <h2>ساهم معنا في صنع الفرق</h2>
        <p style="color:rgba(255,255,255,0.85); max-width:520px; margin:0 auto 1rem;">تبرعكم نور يمحو جزءا من تعب الأيتام. كل مساهمة، مهما كان حجمها، تصنع فرقا حقيقيا.</p>
        <div class="hero-actions">
            <a href="don.php" class="btn-white">تبرع الآن</a>
            <a href="join_us.php" class="btn-outline-white">كن متطوعا</a>
        </div>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>
