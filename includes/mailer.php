<?php
/**
 * includes/mailer.php
 *
 * Fonction d'envoi d'email via PHPMailer (installation manuelle, sans Composer).
 * Nécessite les 3 fichiers PHPMailer dans includes/PHPMailer/src/ :
 *   - PHPMailer.php
 *   - SMTP.php
 *   - Exception.php
 * (voir le guide d'installation fourni séparément)
 */

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envoie un email HTML.
 *
 * @param string $to Email du destinataire
 * @param string $toName Nom du destinataire
 * @param string $subject Sujet de l'email
 * @param string $bodyHtml Contenu HTML de l'email
 * @return bool true si envoyé avec succès, false sinon
 */
function sendMail(string $to, string $toName, string $subject, string $bodyHtml): bool
{
    $mail = new PHPMailer(true);

    try {
        // Configuration serveur SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Expéditeur / destinataire
        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($to, $toName);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // On log l'erreur sans bloquer le reste du script (l'email n'est jamais critique)
        error_log('Erreur envoi email : ' . $mail->ErrorInfo);
        return false;
    }
}
