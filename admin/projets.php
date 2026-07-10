<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// Ajout rapide d'une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_categorie'])) {
    $nom = trim($_POST['new_categorie']);
    if ($nom !== '') {
        $pdo->prepare("INSERT INTO categories_projets (nom) VALUES (?)")->execute([$nom]);
    }
    header('Location: projets.php');
    exit;
}

// Suppression d'un projet
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM projets WHERE id = ?")->execute([$id]); // CASCADE supprime photos + historique liés
    header('Location: projets.php');
    exit;
}

// Filtres
$filtreCategorie = $_GET['categorie'] ?? '';
$filtreStatut = $_GET['statut'] ?? '';

$sql = "SELECT p.*, c.nom AS categorie_nom,
               (SELECT url FROM photos_projets WHERE projet_id = p.id ORDER BY date_ajout LIMIT 1) AS photo_principale
        FROM projets p
        LEFT JOIN categories_projets c ON p.categorie_id = c.id
        WHERE 1=1";
$params = [];

if ($filtreCategorie !== '') {
    $sql .= " AND p.categorie_id = ?";
    $params[] = $filtreCategorie;
}
if ($filtreStatut !== '') {
    $sql .= " AND p.statut = ?";
    $params[] = $filtreStatut;
}
$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projets = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories_projets ORDER BY nom")->fetchAll();

include '../includes/header.php';
?>

<h2>Gestion des Projets</h2>

<div class="projets-toolbar">
    <a class="btn-add" href="projet_form.php">+ Nouveau projet</a>

    <form method="GET" class="filter-form">
        <select name="categorie" onchange="this.form.submit()">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $filtreCategorie == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="statut" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="en_cours" <?= $filtreStatut === 'en_cours' ? 'selected' : '' ?>>En cours</option>
            <option value="termine" <?= $filtreStatut === 'termine' ? 'selected' : '' ?>>Terminé</option>
            <option value="suspendu" <?= $filtreStatut === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
        </select>
    </form>
</div>

<details class="categorie-manager">
    <summary>Gérer les catégories (<?= count($categories) ?>)</summary>
    <ul>
        <?php foreach ($categories as $c): ?>
            <li><?= htmlspecialchars($c['nom']) ?></li>
        <?php endforeach; ?>
    </ul>
    <form method="POST" class="inline-form">
        <input type="text" name="new_categorie" placeholder="Nouvelle catégorie (ex: Parrainage orphelins)" required>
        <button type="submit">Ajouter</button>
    </form>
</details>

<div class="projets-grid">
    <?php foreach ($projets as $p): ?>
        <div class="projet-card">
            <?php if ($p['photo_principale']): ?>
                <img src="../uploads/<?= htmlspecialchars($p['photo_principale']) ?>" class="projet-thumb">
            <?php else: ?>
                <div class="projet-thumb placeholder">Pas de photo</div>
            <?php endif; ?>

            <div class="projet-body">
                <span class="badge badge-<?= $p['statut'] ?>"><?= $p['statut'] ?></span>
                <h3><?= htmlspecialchars($p['titre']) ?></h3>
                <p class="projet-meta"><?= htmlspecialchars($p['categorie_nom'] ?? 'Sans catégorie') ?> · Cible : <?= htmlspecialchars($p['cible_type']) ?></p>

                <div class="progress-bar">
                    <?php
                        $pct = $p['budget_prevu'] > 0 ? min(100, round(($p['budget_collecte'] / $p['budget_prevu']) * 100)) : 0;
                    ?>
                    <div class="progress-fill" style="width: <?= $pct ?>%;"></div>
                </div>
                <p class="projet-budget"><?= number_format($p['budget_collecte'], 0) ?> / <?= number_format($p['budget_prevu'], 0) ?> MAD (<?= $pct ?>%)</p>

                <div class="projet-actions">
                    <a href="projet_form.php?id=<?= $p['id'] ?>">Modifier</a>
                    <a href="projet_historique.php?id=<?= $p['id'] ?>">Historique</a>
                    <a href="projets.php?delete=<?= $p['id'] ?>" onclick="return confirm('Supprimer ce projet et tout son historique ?');">Supprimer</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($projets)): ?>
        <p class="info-note">Aucun projet pour l'instant. Cliquez sur "+ Nouveau projet" pour commencer.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
