<?php
require_once '../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT p.*, c.nom AS categorie_nom FROM projets p 
     LEFT JOIN categories_projets c ON p.categorie_id = c.id WHERE p.id = ?"
);
$stmt->execute([$id]);
$projet = $stmt->fetch();

if (!$projet) {
    require_once '../includes/header_public.php';
    echo '<section class="section"><div class="container"><p class="badge-empty">المشروع غير موجود.</p></div></section>';
    include '../includes/footer_public.php';
    exit;
}

// Toutes les éditions VALIDÉES de ce projet, la plus récente en premier
// (numéro d'édition + date de début, comme critères de nouveauté)
$stmt = $pdo->prepare(
    "SELECT * FROM projet_editions 
     WHERE projet_id = ? AND statut = 'validee' 
     ORDER BY numero_edition DESC, date_debut DESC"
);
$stmt->execute([$id]);
$editions = $stmt->fetchAll();

// L'édition actuelle = la première de la liste (déjà triée par la + récente).
// Les autres constituent la frise chronologique de l'historique.
$editionActuelle = $editions[0] ?? null;
$editionsPassees = array_slice($editions, 1);

// Mises à jour validées, groupées par édition (frise d'évolution)
$majParEdition = [];
$photosOriginales = []; // [edition_id] => photos de la description initiale (maj_id NULL)
$photosParMaj = [];     // [maj_id] => photos rattachées à cette mise à jour

if (!empty($editions)) {
    $editionIds = array_column($editions, 'id');
    $placeholders = implode(',', array_fill(0, count($editionIds), '?'));

    $stmtMaj = $pdo->prepare(
        "SELECT * FROM mises_a_jour_edition WHERE edition_id IN ($placeholders) AND statut = 'validee' ORDER BY date_ajout ASC"
    );
    $stmtMaj->execute($editionIds);
    foreach ($stmtMaj->fetchAll() as $maj) {
        $majParEdition[$maj['edition_id']][] = $maj;
    }

    $stmtPhotos = $pdo->prepare("SELECT * FROM photos_projets WHERE edition_id IN ($placeholders) ORDER BY date_ajout");
    $stmtPhotos->execute($editionIds);
    foreach ($stmtPhotos->fetchAll() as $photo) {
        if ($photo['maj_id'] === null) {
            $photosOriginales[$photo['edition_id']][] = $photo;
        } else {
            $photosParMaj[$photo['maj_id']][] = $photo;
        }
    }
}
$photosActuelles = $editionActuelle ? ($photosOriginales[$editionActuelle['id']] ?? []) : [];

$stmt = $pdo->prepare("SELECT * FROM commentaires_projets WHERE projet_id = ? ORDER BY date_commentaire DESC");
$stmt->execute([$id]);
$commentaires = $stmt->fetchAll();

$cibleLabels = [
    'famille' => 'عائلة', 'village' => 'قرية', 'ecole' => 'مدرسة', 'orphelin' => 'اليتيم',
    'malades' => 'مرضى', 'hafaza_quran' => 'حفظة القرآن', 'veuves' => 'أرامل',
];
$statutLabels = ['en_cours' => 'قيد التنفيذ', 'termine' => 'مكتمل', 'suspendu' => 'متوقف مؤقتا'];

$pageTitle = $projet['titre'];
include '../includes/header_public.php';

$pct = 0;
if ($editionActuelle && $editionActuelle['budget_prevu'] > 0) {
    $pct = min(100, round(($editionActuelle['budget_collecte'] / $editionActuelle['budget_prevu']) * 100));
}
?>

<section class="page-hero orange">
    <div class="container">
        <h1><?= htmlspecialchars($projet['titre']) ?></h1>
        <p><?= htmlspecialchars($projet['categorie_nom'] ?? '') ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="breadcrumb"><a href="projets.php">مشاريعنا</a> ← <?= htmlspecialchars($projet['titre']) ?></p>

        <?php if (!$editionActuelle): ?>

            <p class="badge-empty">لا تتوفر بعد أي معلومات منشورة عن هذا المشروع. سيتم نشر التفاصيل قريبا.</p>

        <?php else: ?>

        <div class="projet-detail-hero" id="edition-<?= $editionActuelle['numero_edition'] ?>">
            <div>
                <?php if (!empty($photosActuelles)): ?>
                    <img src="../uploads/<?= htmlspecialchars($photosActuelles[0]['url']) ?>" class="projet-gallery-main" id="projetMainImage">
                    <?php if (count($photosActuelles) > 1): ?>
                    <div class="projet-gallery-thumbs">
                        <?php foreach ($photosActuelles as $ph): ?>
                            <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="project-card-img placeholder" style="height:340px; border-radius:14px;">لا توجد صورة</div>
                <?php endif; ?>
            </div>

            <div class="projet-info-card">
                <span class="project-tag"><?= htmlspecialchars($statutLabels[$projet['statut']] ?? $projet['statut']) ?></span>
                <span class="project-tag" style="background:var(--gold-soft, #F0B429);">الإصدار الحالي #<?= $editionActuelle['numero_edition'] ?></span>

                <div class="budget-figures">
                    <span><?= number_format($editionActuelle['budget_collecte'], 0) ?> د.م.</span>
                    <span style="color:var(--ink-soft); font-weight:600;">من <?= number_format($editionActuelle['budget_prevu'], 0) ?> د.م.</span>
                </div>
                <div class="project-progress"><div class="project-progress-fill" style="width:<?= $pct ?>%;"></div></div>
                <div class="project-progress-label">
                    <?= number_format($editionActuelle['budget_collecte'],0) ?> / <?= number_format($editionActuelle['budget_prevu'],0) ?> د.م. (<?= $pct ?>%)
                    <?php if ($editionActuelle['budget_collecte'] > $editionActuelle['budget_prevu']): ?>
                        <span class="badge-goal-reached">🎉 الهدف تحقق</span>
                    <?php endif; ?>
                </div>

                <ul class="projet-meta-list">
                    <li><span>الفئة المستهدفة</span><span><?= htmlspecialchars($cibleLabels[$projet['cible_type']] ?? $projet['cible_type']) ?></span></li>
                    <?php if ($projet['cible_details']): ?>
                    <li><span>التفاصيل</span><span><?= htmlspecialchars($projet['cible_details']) ?></span></li>
                    <?php endif; ?>
                    <?php if ($editionActuelle['date_debut']): ?>
                    <li><span>تاريخ الانطلاق</span><span><?= htmlspecialchars($editionActuelle['date_debut']) ?></span></li>
                    <?php endif; ?>
                </ul>

                <a href="don.php?projet_id=<?= $projet['id'] ?>" class="btn-donate-full">تبرع لهذا المشروع 🤲</a>
            </div>
        </div>

        <div class="projet-description">
            <h2>عن الإصدار الحالي</h2>
            <p style="font-size:1.05rem; white-space:pre-line;"><?= htmlspecialchars($editionActuelle['description']) ?></p>
        </div>

        <?php /* NOUVEAU : mises à jour validées de l'édition actuelle */ ?>
        <?php if (!empty($majParEdition[$editionActuelle['id']])): ?>
        <div class="evolution-feed">
            <h3>آخر المستجدات</h3>
            <?php foreach ($majParEdition[$editionActuelle['id']] as $maj): ?>
                <div class="evolution-item">
                    <span class="evolution-date"><?= date('d/m/Y', strtotime($maj['date_ajout'])) ?></span>
                    <p style="white-space:pre-line;"><?= nl2br(htmlspecialchars($maj['contenu'])) ?></p>
                    <?php if (!empty($photosParMaj[$maj['id']])): ?>
                        <div class="evolution-photos">
                            <?php foreach ($photosParMaj[$maj['id']] as $ph): ?>
                                <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- ================= FRISE CHRONOLOGIQUE DES ÉDITIONS PASSÉES ================= -->
        <?php if (!empty($editionsPassees)): ?>
        <div class="editions-timeline-section">
            <h2>مسار المشروع عبر السنين</h2>
            <div class="editions-timeline">
                <?php foreach ($editionsPassees as $ed):
                    $photosEd = $photosOriginales[$ed['id']] ?? [];
                    $pctEd = $ed['budget_prevu'] > 0 ? min(100, round(($ed['budget_collecte'] / $ed['budget_prevu']) * 100)) : 0;
                    $extrait = mb_substr($ed['description'], 0, 140) . (mb_strlen($ed['description']) > 140 ? '...' : '');
                ?>
                <details class="edition-timeline-item" id="edition-<?= $ed['numero_edition'] ?>">
                    <summary class="edition-timeline-summary">
                        <?php if (!empty($photosEd)): ?>
                            <img src="../uploads/<?= htmlspecialchars($photosEd[0]['url']) ?>" class="edition-timeline-cover">
                        <?php else: ?>
                            <div class="edition-timeline-cover placeholder">لا صورة</div>
                        <?php endif; ?>
                        <div class="edition-timeline-summary-body">
                            <span class="edition-timeline-badge">الإصدار #<?= $ed['numero_edition'] ?></span>
                            <?php if ($ed['date_debut']): ?>
                                <span class="edition-timeline-date"><?= htmlspecialchars($ed['date_debut']) ?></span>
                            <?php endif; ?>
                            <p class="edition-timeline-excerpt"><?= htmlspecialchars($extrait) ?></p>
                        </div>
                        <span class="edition-timeline-arrow"></span>
                    </summary>

                    <div class="edition-timeline-expanded">
                        <div class="edition-timeline-expanded-grid">
                            <?php /* CORRIGÉ : toutes les photos originales s'affichent désormais, pas une seule */ ?>
                            <?php if (!empty($photosEd)): ?>
                                <div class="edition-timeline-gallery-full">
                                    <?php foreach ($photosEd as $ph): ?>
                                        <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="edition-timeline-photo placeholder">لا توجد صورة</div>
                            <?php endif; ?>

                            <div class="projet-info-card">
                                <span class="project-tag" style="background:var(--gold-soft, #F0B429);">الإصدار #<?= $ed['numero_edition'] ?></span>
                                <div class="budget-figures">
                                    <span><?= number_format($ed['budget_collecte'], 0) ?> د.م.</span>
                                    <span style="color:var(--ink-soft); font-weight:600;">من <?= number_format($ed['budget_prevu'], 0) ?> د.م.</span>
                                </div>
                                <div class="project-progress"><div class="project-progress-fill" style="width:<?= $pctEd ?>%;"></div></div>
                                <div class="project-progress-label">
                                    <?= number_format($ed['budget_collecte'],0) ?> / <?= number_format($ed['budget_prevu'],0) ?> د.م. (<?= $pctEd ?>%)
                                    <?php if ($ed['budget_collecte'] > $ed['budget_prevu']): ?>
                                        <span class="badge-goal-reached">🎉 الهدف تحقق</span>
                                    <?php endif; ?>
                                </div>
                                <ul class="projet-meta-list">
                                    <?php if ($ed['date_debut']): ?>
                                    <li><span>تاريخ الانطلاق</span><span><?= htmlspecialchars($ed['date_debut']) ?></span></li>
                                    <?php endif; ?>
                                    <?php if ($ed['date_fin']): ?>
                                    <li><span>تاريخ الانتهاء</span><span><?= htmlspecialchars($ed['date_fin']) ?></span></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="projet-description">
                            <h3>الوصف</h3>
                            <p style="white-space:pre-line;"><?= htmlspecialchars($ed['description']) ?></p>
                        </div>

                        <?php /* NOUVEAU : mises à jour validées de cette édition passée */ ?>
                        <?php if (!empty($majParEdition[$ed['id']])): ?>
                        <div class="evolution-feed">
                            <h3>آخر المستجدات</h3>
                            <?php foreach ($majParEdition[$ed['id']] as $maj): ?>
                                <div class="evolution-item">
                                    <span class="evolution-date"><?= date('d/m/Y', strtotime($maj['date_ajout'])) ?></span>
                                    <p style="white-space:pre-line;"><?= nl2br(htmlspecialchars($maj['contenu'])) ?></p>
                                    <?php if (!empty($photosParMaj[$maj['id']])): ?>
                                        <div class="evolution-photos">
                                            <?php foreach ($photosParMaj[$maj['id']] as $ph): ?>
                                                <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ================= COMMENTAIRES ================= -->
        <div class="comments-section">
            <h2>التعليقات (<?= count($commentaires) ?>)</h2>

            <form method="POST" action="comment_action.php" class="comment-form">
                <input type="hidden" name="projet_id" value="<?= $projet['id'] ?>">
                <input type="text" name="nom" placeholder="اسمك" required maxlength="150">
                <input type="email" name="email" placeholder="بريدك الإلكتروني (لن يظهر للعموم)" required maxlength="150">
                <textarea name="message" placeholder="اكتب تعليقك هنا..." rows="3" required maxlength="1000"></textarea>
                <button type="submit">نشر التعليق</button>
            </form>

            <div class="comments-list">
                <?php foreach ($commentaires as $c): ?>
                    <div class="comment-item">
                        <div class="comment-avatar"><?= htmlspecialchars(mb_substr($c['nom'], 0, 1)) ?></div>
                        <div class="comment-body">
                            <div class="comment-header">
                                <strong><?= htmlspecialchars($c['nom']) ?></strong>
                                <span class="comment-date"><?= htmlspecialchars($c['date_commentaire']) ?></span>
                            </div>
                            <p><?= nl2br(htmlspecialchars($c['message'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($commentaires)): ?>
                    <p class="badge-empty">لا توجد تعليقات بعد. كن أول من يعلق!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer_public.php'; ?>
