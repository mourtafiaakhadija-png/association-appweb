<?php
/**
 * Affiche un message générique à l'utilisateur, et garde le détail technique
 * uniquement dans un fichier de log (jamais visible publiquement).
 */
function gererErreur(Exception $e, string $messageUtilisateur = "حدث خطأ. يرجى المحاولة مرة أخرى."): void
{
    // Le détail réel part dans un log serveur, jamais affiché à l'écran
    error_log('[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage());

    http_response_code(500);
    die($messageUtilisateur);
}