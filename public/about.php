<?php
require_once '../config/db.php';
$pageTitle = 'من نحن';
include '../includes/header_public.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>من نحن</h1>
        <p>تعرفوا على قصتنا، رسالتنا، وأهدافنا في خدمة اليتيم والمحتاج</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">تعريف بالجمعية</span>
            <h2>قصتنا</h2>
        </div>
        <p style="max-width:780px; margin:0 auto; text-align:center; font-size:1.05rem;">
            جمعية الجيل المبدع هي جمعية تأسست سنة 2020 بمدينة تارودانت، دوار أولاد عيسى، قيادة إكلي.
            تهدف إلى مساعدة الأرامل والأيتام وتوفير الرعاية المادية والصحية والتعليمية والتربوية لأسرة اليتيم عن قرب في وسطه الأسري.
            كما لها أهداف أخرى منها الاهتمام بتحفيظ القرآن الكريم وتدريس العلوم الشرعية للناشئة، وغيرها من الأعمال الخيرية المعروضة في هذا الموقع.
        </p>
        <p style="max-width:780px; margin:1rem auto 0; text-align:center; font-size:1.05rem;">
            تجعل جمعية الجيل المبدع على رأس أولوياتها رعاية اليتيم في وسطه الأسري عبر تقديم مجموعة من الخدمات الموسمية التي يستفيد منها،
            والطموح لكفالة اليتيم في أسرته من خلال مشروع "رافق نبيك بكفالة اليتيم" وذلك من خلال تقديم كفالة شهرية.
            كما تهتم بالكتاتيب القرآنية بإقليم تارودانت، حيث تسعى إلى بناء كتاب قرآني بداخلية للذكور والإناث، يضم جميع المرافق الضرورية للطلبة والطالبات.
        </p>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="values-grid">
            <div class="value-card">
                <h3><i class="fa-solid fa-bullseye " style="color: var(--gold);"></i> رؤيتنا</h3>
                <p>مجتمع متضامن تُصان فيه كرامة اليتيم والمحتاج، ويجد فيه كل محتاج يد العون والرعاية الكاملة داخل بيئته الأسرية والاجتماعية، بروح من العطاء والتطوع المستمر.</p>
            </div>
            <div class="value-card">
                <h3><i class="fa-solid fa-hand-holding-heart" style="color: var(--gold);"></i> رسالتنا</h3>
                <p>مساعدة الأرامل والأيتام وتوفير الرعاية المادية والصحية والتعليمية والتربوية لأسرة اليتيم عن قرب في وسطها الأسري، والاهتمام بتحفيظ القرآن الكريم وتدريس العلوم الشرعية للناشئة، ونقل أمانة الأمة عبر إطلاق مشاريع خيرية متنوعة.</p>
            </div>
            <div class="value-card">
                <h3><i class="fa-solid fa-flag" style="color: var(--gold);"></i> أهدافنا</h3>
                <p>
                    • توفير كفالة شهرية تُلبي الحاجيات الأساسية لليتيم<br>
                    • دعم التمدرس والحد من الهدر المدرسي<br>
                    • المواكبة الاجتماعية والنفسية للحالات المستفيدة<br>
                    • إدماج الأيتام في أنشطة تربوية وترفيهية هادفة<br>
                    • الاهتمام بتحفيظ القرآن الكريم والكتاتيب القرآنية
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">شركاؤنا</span>
            <h2>بفضل الله ثم بفضلكم</h2>
        </div>
        <?php
        $collaborateurs = $pdo->query("SELECT * FROM collaborateurs ORDER BY nom")->fetchAll();
        ?>
        <?php if (empty($collaborateurs)): ?>
            <p class="badge-empty">سيتم عرض شركائنا هنا قريبا.</p>
        <?php else: ?>
        <div class="team-grid">
            <?php foreach ($collaborateurs as $c): ?>
                <div class="team-card">
                    <?php if ($c['logo']): ?>
                        <img src="../uploads/<?= htmlspecialchars($c['logo']) ?>" style="object-fit:contain; padding:1.5rem; background:#fff;">
                    <?php endif; ?>
                    <div class="team-body">
                        <h3><?= htmlspecialchars($c['nom']) ?></h3>
                        <p><?= htmlspecialchars($c['description'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>
