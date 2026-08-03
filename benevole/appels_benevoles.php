<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/auth_check_benevole.php';
require_once '../config/db.php';

$benevoleId = $_SESSION['benevole_id'];

// Toutes les éditions publiées où l'admin a ouvert un appel à bénévoles,
// peu importe le projet — n'importe quel bénévole peut se porter volontaire
$stmt = $pdo->prepare(
    "SELECT e.id AS edition_id, e.numero_edition, e.description, e.date_debut, e.date_fin,
            p.id AS projet_id, p.titre,
            pc.statut AS ma_participation
     FROM projet_editions e
     JOIN projets p ON p.id = e.projet_id
     LEFT JOIN participations_comite pc ON pc.edition_id = e.id AND pc.user_id = ?
     WHERE e.statut = 'validee' AND e.appel_benevoles_ouvert = 1
     ORDER BY e.date_debut ASC"
);
$stmt->execute([$benevoleId]);
$appels = $stmt->fetchAll();

include '../includes/header_benevole.php';
?>

<h2>نداءات التطوع المفتوحة</h2>
<p class="info-note">هذه المشاريع تبحث حاليا عن متطوعين. صرحوا بتوفركم وستقوم الإدارة بتأكيد اللجنة النهائية.</p>

<div class="benevole-projets-grid">
    <?php foreach ($appels as $a): ?>
        <div class="benevole-projet-card">
            <h3><?= htmlspecialchars($a['titre']) ?> <small>#<?= $a['numero_edition'] ?></small></h3>
            <p style="font-size:0.9rem; color:#555;"><?= htmlspecialchars(mb_substr($a['description'], 0, 120)) ?>...</p>
            <?php if ($a['date_debut']): ?>
                <p class="benevole-stat"><i class="fa-solid fa-calendar-days"></i> <?= htmlspecialchars($a['date_debut']) ?><?= $a['date_fin'] ? ' → ' . htmlspecialchars($a['date_fin']) : '' ?></p>
            <?php endif; ?>

            <?php if ($a['ma_participation'] === 'confirme'): ?>
                <span class="badge badge-validee"><i class="fa-solid fa-circle-check"></i> أنتم عضو مؤكد في هذا الفريق</span>
           <?php elseif ($a['ma_participation'] === 'disponible'): ?>
                <span class="badge badge-en_attente_validation"><i class="fa-solid fa-hourglass-start"></i> تم تسجيل توفركم، بانتظار تأكيد الإدارة</span>
            <?php elseif ($a['ma_participation'] === 'non_retenu'): ?>
                <span class="badge" style="background:#6b7280;">لم يتم اختياركم لهذا الفريق هذا المرة، شكرا على تفاعلكم</span>
            <?php else: ?>
                <form method="POST" action="appel_action.php">
                    <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                    <input type="hidden" name="edition_id" value="<?= $a['edition_id'] ?>">
                    <button type="submit" class="btn-add"><i class="fa-solid fa-hand"></i> أنا متوفر لهذا المشروع</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (empty($appels)): ?>
        <p class="badge-empty">لا توجد نداءات تطوع مفتوحة حاليا.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer_benevole.php'; ?>