<?php
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/../config/mail.php';

function genererJetonDesabonnement(string $email): string
{
    return hash('sha256', $email . APP_SECRET_KEY);
}

function estDesabonne(PDO $pdo, string $email): bool
{
    $stmt = $pdo->prepare("SELECT 1 FROM donateurs_desabonnes WHERE email = ?");
    $stmt->execute([$email]);
    return (bool) $stmt->fetchColumn();
}

function piedDePageDesabonnement(string $email): string
{
    $token = genererJetonDesabonnement($email);
    $lien = SITE_URL . "/desabonnement.php?email=" . urlencode($email) . "&token=" . $token;
    return "<hr><p style='font-size:12px;color:#888;'>لم ترغبوا في تلقي هذه الإشعارات؟ <a href='$lien'>اضغطوا هنا لإلغاء الاشتراك</a>.</p>";
}

/** Notifie les donateurs d'UN projet précis (nouveau contenu : description/photos) */
function notifierDonateursProjet(PDO $pdo, int $projetId, string $projetTitre): void
{
    $stmt = $pdo->prepare(
        "SELECT DISTINCT email_donateur, nom_donateur FROM dons
         WHERE projet_id = ? AND email_donateur IS NOT NULL AND email_donateur != ''"
    );
    $stmt->execute([$projetId]);
    $donateurs = $stmt->fetchAll();

    $lien = SITE_URL . "/projet_detail.php?id=" . $projetId;

    foreach ($donateurs as $d) {
        if (estDesabonne($pdo, $d['email_donateur'])) continue;

        $sujet = "جديد في المشروع الذي دعمتموه: " . $projetTitre;
        $corps = "
            <p>مرحبا " . htmlspecialchars($d['nom_donateur']) . "،</p>
            <p>هناك مستجدات جديدة في مشروع \"" . htmlspecialchars($projetTitre) . "\" الذي ساهمتم فيه بتبرعكم (صور جديدة و/أو تفاصيل محدثة).</p>
            <p><a href='$lien'>اضغطوا هنا للاطلاع على المستجدات</a></p>
            <p>شكرا لكم على دعمكم المستمر ❤️</p>
            " . piedDePageDesabonnement($d['email_donateur']) . "
        ";
        sendMail($d['email_donateur'], $d['nom_donateur'], $sujet, $corps);
    }
}

/** Notifie TOUS les anciens donateurs du site : un nouveau projet est ouvert aux dons */
function notifierNouveauProjetOuvert(PDO $pdo, int $projetId, string $projetTitre): void
{
    $stmt = $pdo->query(
        "SELECT DISTINCT email_donateur, nom_donateur FROM dons
         WHERE email_donateur IS NOT NULL AND email_donateur != ''"
    );
    $donateurs = $stmt->fetchAll();

    $lien = SITE_URL . "/projet_detail.php?id=" . $projetId;

    foreach ($donateurs as $d) {
        if (estDesabonne($pdo, $d['email_donateur'])) continue;

        $sujet = "مشروع جديد يحتاج دعمكم: " . $projetTitre;
        $corps = "
            <p>مرحبا " . htmlspecialchars($d['nom_donateur']) . "،</p>
            <p>أطلقت جمعية الجيل المبدع مشروعا جديدا: \"" . htmlspecialchars($projetTitre) . "\"، وبإمكانكم الآن المساهمة فيه.</p>
            <p><a href='$lien'>اضغطوا هنا لاكتشاف المشروع والتبرع</a></p>
            <p>شكرا لكم على دعمكم المستمر ❤️</p>
            " . piedDePageDesabonnement($d['email_donateur']) . "
        ";
        sendMail($d['email_donateur'], $d['nom_donateur'], $sujet, $corps);
    }
}