<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$filtre = $_GET['filtre'] ?? 'en_attente';

if ($filtre === 'en_attente') {
    $candidatures = $pdo->query(
        "SELECT * FROM candidatures_benevoles WHERE statut = 'en_attente' ORDER BY date_candidature DESC"
    )->fetchAll();
} elseif ($filtre === 'acceptees') {
    $candidatures = $pdo->query(
        "SELECT * FROM candidatures_benevoles WHERE statut = 'acceptee' ORDER BY date_reponse DESC"
    )->fetchAll();
} elseif ($filtre === 'rejetees') {
    $candidatures = $pdo->query(
        "SELECT * FROM candidatures_benevoles WHERE statut = 'rejetee' ORDER BY date_reponse DESC"
    )->fetchAll();
} else {
    $candidatures = $pdo->query(
        "SELECT * FROM candidatures_benevoles ORDER BY date_candidature DESC"
    )->fetchAll();
}

$nbEnAttente = $pdo->query("SELECT COUNT(*) FROM candidatures_benevoles WHERE statut = 'en_attente'")->fetchColumn();

include '../includes/header.php';
?>

<h2>طلبات التطوع</h2>

<nav class="rh-tabs">
    <a href="?filtre=en_attente" class="<?= $filtre === 'en_attente' ? 'active' : '' ?>">قيد الانتظار (<?= $nbEnAttente ?>)</a>
    <a href="?filtre=acceptees" class="<?= $filtre === 'acceptees' ? 'active' : '' ?>">المقبولة</a>
    <a href="?filtre=rejetees" class="<?= $filtre === 'rejetees' ? 'active' : '' ?>">المرفوضة</a>
    <a href="?filtre=tous" class="<?= $filtre === 'tous' ? 'active' : '' ?>">جميع الطلبات</a>
</nav>

<table class="rh-table">
    <thead>
        <tr><th>التاريخ</th><th>الاسم</th><th>الاتصال</th><th>المدينة</th><th>الدافع</th><th>الحالة</th><th>الإجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($candidatures as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['date_candidature']) ?></td>
            <td>
                <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;" class="rh-cell-flex">
                    <?php if ($c['photo']): ?>
                        <img src="../uploads/<?= htmlspecialchars($c['photo']) ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                    <?php endif; ?>
                    <div>
                        <strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong><br>
                        <small><?= htmlspecialchars($c['profession'] ?: '-') ?> — <?= htmlspecialchars($c['niveau_etude'] ?: '-') ?></small>
                    </div>
                </div>
            </td>
            <td><?= htmlspecialchars($c['email']) ?><br><small><?= htmlspecialchars($c['telephone'] ?: '-') ?></small></td>
            <td><?= htmlspecialchars($c['ville'] ?: '-') ?></td>
            <td style="max-width:280px;">
                <?= nl2br(htmlspecialchars(mb_substr($c['motivation'], 0, 100))) ?><?= mb_strlen($c['motivation']) > 100 ? '...' : '' ?>
                <details class="candidature-details">
                    <summary>عرض التفاصيل الكاملة</summary>
                    <p><strong>الدافع الكامل:</strong><br><?= nl2br(htmlspecialchars($c['motivation'])) ?></p>
                    <?php if ($c['competences']): ?>
                        <p><strong>المهارات:</strong><br><?= nl2br(htmlspecialchars($c['competences'])) ?></p>
                    <?php endif; ?>
                    <?php if ($c['experiences']): ?>
                        <p><strong>التجارب السابقة:</strong><br><?= nl2br(htmlspecialchars($c['experiences'])) ?></p>
                    <?php endif; ?>
                </details>
            </td>
            <td>
                <?php if ($c['statut'] === 'en_attente'): ?>
                    <i class="fa-solid fa-hourglass-start"></i> قيد الانتظار
                <?php elseif ($c['statut'] === 'acceptee'): ?>
                    <i class="fa-solid fa-circle-check"></i> مقبولة
                <?php else: ?>
                    <i class="fa-solid fa-circle-xmark"></i> مرفوضة
                <?php endif; ?>
            </td>
            <td>
                <?php if ($c['statut'] === 'en_attente'): ?>
                    <form method="POST" action="candidature_action.php" style="display:inline;" onsubmit="return confirm('...');">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <input type="hidden" name="action" value="accepter">
                        <button type="submit" class="link-button">قبول</button>
                    </form> |
                    <form method="POST" action="candidature_action.php" style="display:inline;" onsubmit="return confirm('Rejeter cette candidature ?');">
                        <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <input type="hidden" name="action" value="rejeter">
                        <button type="submit" class="link-button">رفض</button>
                    </form>
                <?php else: ?>
                    <small>تم الرد بتاريخ <?= htmlspecialchars($c['date_reponse']) ?></small>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($candidatures)): ?>
        <tr><td colspan="7">لا يوجد أي طلب تطوع حالياً.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>