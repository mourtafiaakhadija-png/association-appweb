<?php
/**
 * Protection CSRF : un jeton unique par session, vérifié à chaque action sensible.
 */
function genererJetonCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifierJetonCsrf(): void
{
    $recu = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $recu)) {
        http_response_code(403);
        die("طلب غير صالح (انتهت صلاحية الجلسة). يرجى إعادة تحميل الصفحة والمحاولة مجددا.");
    }
}