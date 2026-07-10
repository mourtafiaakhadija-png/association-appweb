<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$tab = $_GET['tab'] ?? 'bureau';

// Suppression (depuis les liens "Supprimer")
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = (int) $_GET['delete'];
    if ($_GET['type'] === 'bureau') {
        // On récupère le user_id lié pour supprimer aussi le compte user
        $stmt = $pdo->prepare("SELECT user_id FROM bureau_membres WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$row['user_id']]);
            // bureau_membres est supprimé automatiquement via ON DELETE CASCADE
        }
    } elseif ($_GET['type'] === 'collaborateur') {
        $pdo->prepare("DELETE FROM collaborateurs WHERE id = ?")->execute([$id]);
    }
    header('Location: rh.php?tab=' . urlencode($tab));
    exit;
}

// Récupération des données selon l'onglet actif
$bureau = $pdo->query(
    "SELECT bm.id, bm.fonction, bm.photo, bm.bio, u.nom, u.prenom, u.email 
     FROM bureau_membres bm JOIN users u ON bm.user_id = u.id 
     ORDER BY u.nom"
)->fetchAll();

$benevoles = $pdo->query(
    "SELECT id, nom, prenom, email, telephone, statut, created_at 
     FROM users WHERE role = 'benevole' ORDER BY nom"
)->fetchAll();

$donateurs = $pdo->query(
    "SELECT DISTINCT nom_donateur, email_donateur, COUNT(*) as nb_dons, SUM(montant) as total 
     FROM dons GROUP BY nom_donateur, email_donateur ORDER BY total DESC"
)->fetchAll();

$collaborateurs = $pdo->query("SELECT * FROM collaborateurs ORDER BY nom")->fetchAll();

include '../includes/header.php';
?>

<h2>Gestion des Ressources Humaines</h2>

<nav class="rh-tabs">
    <a href="?tab=bureau" class="<?= $tab === 'bureau' ? 'active' : '' ?>">Bureau (<?= count($bureau) ?>)</a>
    <a href="?tab=benevoles" class="<?= $tab === 'benevoles' ? 'active' : '' ?>">Bénévoles (<?= count($benevoles) ?>)</a>
    <a href="?tab=donateurs" class="<?= $tab === 'donateurs' ? 'active' : '' ?>">Donateurs (<?= count($donateurs) ?>)</a>
    <a href="?tab=collaborateurs" class="<?= $tab === 'collaborateurs' ? 'active' : '' ?>">Collaborateurs (<?= count($collaborateurs) ?>)</a>
</nav>

<?php if ($tab === 'bureau'): ?>
    <a class="btn-add" href="rh_form.php?type=bureau">+ Ajouter un membre du bureau</a>
    <table class="rh-table">
        <thead>
            <tr><th>Photo</th><th>Nom</th><th>Fonction</th><th>Email</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($bureau as $m): ?>
            <tr>
                <td><?php if ($m['photo']): ?><img src="../uploads/<?= htmlspecialchars($m['photo']) ?>" class="thumb"><?php endif; ?></td>
                <td><?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></td>
                <td><?= htmlspecialchars($m['fonction']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td>
                    <a href="rh_form.php?type=bureau&id=<?= $m['id'] ?>">Modifier</a> |
                    <a href="rh.php?delete=<?= $m['id'] ?>&type=bureau" onclick="return confirm('Supprimer ce membre ?');">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($bureau)): ?><tr><td colspan="5">Aucun membre du bureau pour l'instant.</td></tr><?php endif; ?>
        </tbody>
    </table>

<?php elseif ($tab === 'benevoles'): ?>
    <p class="info-note">Les bénévoles apparaissent ici automatiquement après acceptation de leur candidature </p>
    <table class="rh-table">
        <thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Statut</th><th>Inscrit le</th></tr></thead>
        <tbody>
        <?php foreach ($benevoles as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['prenom'] . ' ' . $b['nom']) ?></td>
                <td><?= htmlspecialchars($b['email']) ?></td>
                <td><?= htmlspecialchars($b['telephone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($b['statut']) ?></td>
                <td><?= htmlspecialchars($b['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($benevoles)): ?><tr><td colspan="5">Aucun bénévole pour l'instant.</td></tr><?php endif; ?>
        </tbody>
    </table>

<?php elseif ($tab === 'donateurs'): ?>
    <p class="info-note">Les donateurs apparaissent ici automatiquement dès qu'un don est enregistré.</p>
    <table class="rh-table">
        <thead><tr><th>Nom</th><th>Email</th><th>Nombre de dons</th><th>Total donné</th></tr></thead>
        <tbody>
        <?php foreach ($donateurs as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['nom_donateur']) ?></td>
                <td><?= htmlspecialchars($d['email_donateur'] ?? '-') ?></td>
                <td><?= (int) $d['nb_dons'] ?></td>
                <td><?= number_format((float) $d['total'], 2) ?> MAD</td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($donateurs)): ?><tr><td colspan="4">Aucun don pour l'instant.</td></tr><?php endif; ?>
        </tbody>
    </table>

<?php elseif ($tab === 'collaborateurs'): ?>
    <a class="btn-add" href="rh_form.php?type=collaborateur">+ Ajouter un collaborateur</a>
    <table class="rh-table">
        <thead><tr><th>Logo</th><th>Nom</th><th>Description</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($collaborateurs as $c): ?>
            <tr>
                <td><?php if ($c['logo']): ?><img src="../uploads/<?= htmlspecialchars($c['logo']) ?>" class="thumb"><?php endif; ?></td>
                <td><?= htmlspecialchars($c['nom']) ?></td>
                <td><?= htmlspecialchars($c['description'] ?? '-') ?></td>
                <td>
                    <a href="rh_form.php?type=collaborateur&id=<?= $c['id'] ?>">Modifier</a> |
                    <a href="rh.php?delete=<?= $c['id'] ?>&type=collaborateur" onclick="return confirm('Supprimer ce collaborateur ?');">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($collaborateurs)): ?><tr><td colspan="4">Aucun collaborateur pour l'instant.</td></tr><?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
