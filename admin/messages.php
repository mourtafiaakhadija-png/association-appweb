<?php
session_start();
require_once '../includes/i18n_admin.php';
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$filtre = $_GET['filtre'] ?? 'tous';

// Marquer comme traité / non traité (toggle, depuis les liens du tableau)
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $pdo->prepare("UPDATE messages_contact SET traite = NOT traite WHERE id = ?")->execute([$id]);
    header('Location: messages.php?filtre=' . urlencode($filtre));
    exit;
}

// Suppression
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM messages_contact WHERE id = ?")->execute([$id]);
    header('Location: messages.php?filtre=' . urlencode($filtre));
    exit;
}

// Récupération selon le filtre actif
if ($filtre === 'non_traites') {
    $messages = $pdo->query("SELECT * FROM messages_contact WHERE traite = 0 ORDER BY date_envoi DESC")->fetchAll();
} elseif ($filtre === 'traites') {
    $messages = $pdo->query("SELECT * FROM messages_contact WHERE traite = 1 ORDER BY date_envoi DESC")->fetchAll();
} else {
    $messages = $pdo->query("SELECT * FROM messages_contact ORDER BY date_envoi DESC")->fetchAll();
}

$nbNonTraites = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE traite = 0")->fetchColumn();

include '../includes/header.php';
?>

<h2>رسائل التواصل</h2>

<nav class="rh-tabs">
    <a href="?filtre=tous" class="<?= $filtre === 'tous' ? 'active' : '' ?>">جميع الرسائل</a>
    <a href="?filtre=non_traites" class="<?= $filtre === 'non_traites' ? 'active' : '' ?>">غير المعالجة (<?= $nbNonTraites ?>)</a>
    <a href="?filtre=traites" class="<?= $filtre === 'traites' ? 'active' : '' ?>">المعالجة</a>
</nav>

<table class="rh-table">
    <thead>
        <tr><th>التاريخ</th><th>الاسم</th><th>البريد الإلكتروني</th><th>الموضوع</th><th>الرسالة</th><th>الحالة</th><th>الإجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($messages as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['date_envoi']) ?></td>
            <td><?= htmlspecialchars($m['nom']) ?></td>
            <td><?= htmlspecialchars($m['email']) ?></td>
            <td><?= htmlspecialchars($m['sujet'] ?: '-') ?></td>
            <td style="max-width:300px;"><?= nl2br(htmlspecialchars($m['message'])) ?></td>
            <td><?= $m['traite'] ? '✅ تم المعالجة' : '⏳ قيد الانتظار' ?></td>
            <td>
                <a href="?toggle=<?= $m['id'] ?>&filtre=<?= urlencode($filtre) ?>">
                    <?= $m['traite'] ? 'Marquer non traité' : 'Marquer traité' ?>
                </a> |
                <a href="?delete=<?= $m['id'] ?>&filtre=<?= urlencode($filtre) ?>" onclick="return confirm('Supprimer ce message ?');">حذف</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($messages)): ?>
        <tr><td colspan="7">لا يوجد أي رسالة حالياً.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>