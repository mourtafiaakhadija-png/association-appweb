<?php
/**
 * admin/create_admin.php
 *
 * SCRIPT À USAGE UNIQUE : crée le premier compte admin.
 * ⚠️ SUPPRIMEZ ce fichier une fois le compte créé (sécurité).
 */

require_once '../config/db.php';

// ⚠️ Modifiez ces valeurs avant d'exécuter le script
$nom = 'Mourtafiaa';
$prenom = 'Khadija';
$email = 'mourtafiaakhadija@gmail.com';
$motDePasse = 'KH@2007DM35'; 

$hash = password_hash($motDePasse, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "INSERT INTO users (nom, prenom, email, password, role, statut) 
     VALUES (?, ?, ?, ?, 'admin', 'actif')"
);

try {
    $stmt->execute([$nom, $prenom, $email, $hash]);
    echo "✅ Compte admin créé avec succès.<br>";
    echo "Email : $email<br>";
    echo "⚠️ Supprimez ce fichier (create_admin.php) maintenant !";
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
