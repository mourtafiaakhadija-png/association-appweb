<?php
/**
 * Protection contre les tentatives de connexion en boucle (brute force).
 * Après 5 échecs, l'email est bloqué 15 minutes.
 */

const MAX_TENTATIVES = 5;
const DUREE_BLOCAGE_MINUTES = 15;

/**
 * Vérifie si cet email est actuellement bloqué.
 * Retourne le nombre de minutes restantes si bloqué, ou 0 si non bloqué.
 */
function estBloque(PDO $pdo, string $email): int
{
    $stmt = $pdo->prepare("SELECT bloque_jusqua FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
    $bloqueJusqua = $stmt->fetchColumn();

    if ($bloqueJusqua && strtotime($bloqueJusqua) > time()) {
        return (int) ceil((strtotime($bloqueJusqua) - time()) / 60);
    }
    return 0;
}

/**
 * Enregistre une tentative échouée. Bloque l'email si le seuil est dépassé.
 */
function enregistrerEchec(PDO $pdo, string $email): void
{
    $stmt = $pdo->prepare("SELECT tentatives FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
    $tentatives = (int) $stmt->fetchColumn();
    $tentatives++;

    $bloqueJusqua = null;
    $vientDetreBloque = false;
    if ($tentatives >= MAX_TENTATIVES) {
        $bloqueJusqua = date('Y-m-d H:i:s', time() + DUREE_BLOCAGE_MINUTES * 60);
        $vientDetreBloque = true;
    }

    $pdo->prepare(
        "INSERT INTO login_attempts (email, tentatives, derniere_tentative, bloque_jusqua) VALUES (?, ?, NOW(), ?)
         ON DUPLICATE KEY UPDATE tentatives = ?, derniere_tentative = NOW(), bloque_jusqua = ?"
    )->execute([$email, $tentatives, $bloqueJusqua, $tentatives, $bloqueJusqua]);

    if ($vientDetreBloque) {
        envoyerAlerteConnexionSuspecte($pdo, $email);
    }
}

/**
 * Envoie un email d'alerte au vrai propriétaire du compte (si l'email existe),
 * suite à un blocage pour tentatives de connexion répétées.
 */
function envoyerAlerteConnexionSuspecte(PDO $pdo, string $email): void
{
    require_once __DIR__ . '/mailer.php';

    $stmt = $pdo->prepare("SELECT nom, prenom FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) return;  

    $nomComplet = $user['prenom'] . ' ' . $user['nom'];
    $sujet = "تنبيه أمني: محاولات دخول متكررة فاشلة على حسابكم";
    $corps = "
        <p>مرحبا " . htmlspecialchars($nomComplet) . "،</p>
        <p>سجّلنا " . MAX_TENTATIVES . " محاولات دخول فاشلة متتالية على حسابكم (" . htmlspecialchars($email) . ").</p>
        <p>تم حظر الحساب مؤقتا لمدة " . DUREE_BLOCAGE_MINUTES . " دقيقة كإجراء وقائي.</p>
        <p>إذا كنتم أنتم من حاول الدخول ونسيتم كلمة المرور، لا داعي للقلق. أما إذا لم تكونوا أنتم، ننصحكم بتغيير كلمة المرور فور انتهاء مدة الحظر.</p>
    ";
    sendMail($email, $nomComplet, $sujet, $corps);
}
/**
 * Remet le compteur à zéro après une connexion réussie.
 */
function reinitialiserTentatives(PDO $pdo, string $email): void
{
    $pdo->prepare("DELETE FROM login_attempts WHERE email = ?")->execute([$email]);
}