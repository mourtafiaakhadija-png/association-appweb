<?php
/**
 * includes/upload_helper.php
 *
 * Fonction réutilisable pour uploader une image de façon sécurisée.
 * Retourne le nom du fichier généré, ou null si aucune image envoyée.
 * Lance une Exception en cas de fichier invalide.
 */

function handleImageUpload(string $fieldName, string $uploadDir = '../uploads/'): ?string
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // Aucun fichier envoyé, pas grave (ex: modification sans changer la photo)
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erreur lors de l'upload du fichier.");
    }

    // Vérifier le type MIME réel (pas juste l'extension, plus sûr)
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!array_key_exists($mime, $allowedTypes)) {
        throw new Exception("Format d'image non autorisé. Utilisez JPG, PNG ou WEBP.");
    }

    // Limite de taille : 3 Mo
    if ($file['size'] > 3 * 1024 * 1024) {
        throw new Exception("L'image est trop volumineuse (max 3 Mo).");
    }

    $extension = $allowedTypes[$mime];
    $newFileName = uniqid('img_', true) . '.' . $extension;
    $destination = rtrim($uploadDir, '/') . '/' . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Impossible d'enregistrer l'image.");
    }

    return $newFileName;
}
/**
 * Fonction réutilisable pour uploader un document (rapport) de façon sécurisée.
 * Retourne le nom du fichier généré, ou null si aucun fichier envoyé.
 * Lance une Exception en cas de fichier invalide.
 */
function handleDocumentUpload(string $fieldName, string $uploadDir = '../uploads/'): ?string
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // Aucun fichier envoyé, pas grave
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erreur lors de l'upload du fichier.");
    }

    $allowedTypes = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!array_key_exists($mime, $allowedTypes)) {
        throw new Exception("Format de fichier non autorisé. Utilisez PDF, DOC ou DOCX.");
    }

    // Limite de taille : 7 Mo
    if ($file['size'] > 7 * 1024 * 1024) {
        throw new Exception("Le fichier est trop volumineux (max 7 Mo).");
    }

    $extension = $allowedTypes[$mime];
    $newFileName = uniqid('doc_', true) . '.' . $extension;
    $destination = rtrim($uploadDir, '/') . '/' . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Impossible d'enregistrer le fichier.");
    }

    return $newFileName;
}
/**
 * Upload générique pour l'espace documents (tous types : images, PDF, Word, ZIP).
 */
function handleGenericFileUpload(string $fieldName, string $uploadDir = '../uploads/'): ?string
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("خطأ أثناء رفع الملف.");
    }

    $allowedTypes = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!array_key_exists($mime, $allowedTypes)) {
        throw new Exception("صيغة الملف غير مسموحة. الصيغ المقبولة: PDF, Word, ZIP, JPG, PNG, WEBP.");
    }
    if ($file['size'] > 20 * 1024 * 1024) {
        throw new Exception("الملف كبير جدا (الحد الأقصى 20 ميغا).");
    }

    $extension = $allowedTypes[$mime];
    $newFileName = uniqid('doc_', true) . '.' . $extension;
    $destination = rtrim($uploadDir, '/') . '/' . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("تعذر حفظ الملف.");
    }
    return $newFileName;
}