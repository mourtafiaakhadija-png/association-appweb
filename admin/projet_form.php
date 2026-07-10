<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isEdit = $id !== null;

$data = [
    'titre' => '', 'description' => '', 'categorie_id' => '', 'responsable_id' => '',
    'cible_type' => 'famille', 'cible_details' => '', 'budget_prevu' => '', 'budget_collecte' => 0,
    'date_debut' => '', 'date_fin' => '', 'statut' => 'en_cours'
];

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) die("Projet introuvable.");
}

$categories = $pdo->query("SELECT * FROM categories_projets ORDER BY nom")->fetchAll();

// Responsables possibles = membres du bureau + bénévoles actifs
$responsables = $pdo->query(
    "SELECT id, nom, prenom, role FROM users WHERE role IN ('bureau','benevole') ORDER BY nom"
)->fetchAll();

$photos = [];
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM photos_projets WHERE projet_id = ? ORDER BY date_ajout");
    $stmt->execute([$id]);
    $photos = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<h2><?= $isEdit ? 'Modifier le projet' : 'Nouveau projet' ?></h2>

<form method="POST" action="projet_action.php" enctype="multipart/form-data" class="projet-form">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $data['id'] ?>"><?php endif; ?>

    <label>Titre du projet</label>
    <input type="text" name="titre" value="<?= htmlspecialchars($data['titre']) ?>" required>

    <label>Description</label>
    <textarea name="description" rows="5" required><?= htmlspecialchars($data['description']) ?></textarea>

    <div class="form-row">
        <div>
            <label>Catégorie</label>
            <select name="categorie_id">
                <option value="">-- Choisir --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $data['categorie_id'] == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Responsable</label>
            <select name="responsable_id">
                <option value="">-- Aucun --</option>
                <?php foreach ($responsables as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $data['responsable_id'] == $r['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?> (<?= $r['role'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>Type de cible</label>
            <select name="cible_type" required>
                <option value="famille" <?= $data['cible_type'] === 'famille' ? 'selected' : '' ?>>Famille</option>
                <option value="village" <?= $data['cible_type'] === 'village' ? 'selected' : '' ?>>Village</option>
                <option value="ecole" <?= $data['cible_type'] === 'ecole' ? 'selected' : '' ?>>École</option>
                <option value="orphelin" <?= $data['cible_type'] === 'orphelin' ? 'selected' : '' ?>>Orphelin (اليتيم)</option>
            </select>
        </div>
        <div>
            <label>Détails de la cible</label>
            <input type="text" name="cible_details" value="<?= htmlspecialchars($data['cible_details'] ?? '') ?>" placeholder="Ex: 120 orphelins, région de Taroudant">
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>Budget prévu (MAD)</label>
            <input type="number" step="0.01" name="budget_prevu" value="<?= htmlspecialchars($data['budget_prevu']) ?>" required>
        </div>
        <div>
            <label>Budget déjà collecté (MAD)</label>
            <input type="number" step="0.01" name="budget_collecte" value="<?= htmlspecialchars($data['budget_collecte']) ?>">
        </div>
    </div>

    <div class="form-row">
        <div>
            <label>Date de début</label>
            <input type="date" name="date_debut" value="<?= htmlspecialchars($data['date_debut']) ?>">
        </div>
        <div>
            <label>Date de fin</label>
            <input type="date" name="date_fin" value="<?= htmlspecialchars($data['date_fin']) ?>">
        </div>
    </div>

    <label>Statut</label>
    <select name="statut" required>
        <option value="en_cours" <?= $data['statut'] === 'en_cours' ? 'selected' : '' ?>>En cours</option>
        <option value="termine" <?= $data['statut'] === 'termine' ? 'selected' : '' ?>>Terminé</option>
        <option value="suspendu" <?= $data['statut'] === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
    </select>

    <label>Ajouter des photos <?= $isEdit ? '(en plus des photos existantes)' : '' ?></label>
    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>

    <?php if ($isEdit && !empty($photos)): ?>
        <div class="existing-photos">
            <?php foreach ($photos as $ph): ?>
                <div class="existing-photo">
                    <img src="../uploads/<?= htmlspecialchars($ph['url']) ?>">
                    <a href="projet_action.php?delete_photo=<?= $ph['id'] ?>&projet_id=<?= $id ?>" onclick="return confirm('Supprimer cette photo ?');">✕</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <button type="submit"><?= $isEdit ? 'Enregistrer les modifications' : 'Créer le projet' ?></button>
    <a href="projets.php" class="btn-cancel">Annuler</a>
</form>

<?php include '../includes/footer.php'; ?>
