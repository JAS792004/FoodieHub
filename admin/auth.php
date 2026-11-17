
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}

function adminVerifyCsrf() {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['admin_csrf_token'], $token)
    ) {
        http_response_code(403);
        exit("Invalid request. Please refresh the page and try again.");
    }
}

function adminUploadImage($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Image upload failed.");
    }

    if ($file['size'] > 3 * 1024 * 1024) {
        throw new RuntimeException("Image size must not exceed 3 MB.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException("Only JPG, PNG and WEBP images are allowed.");
    }

    $directory = __DIR__ . '/../assets/images/menu/';

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException("Unable to create image directory.");
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($file['tmp_name'], $directory . $filename)) {
        throw new RuntimeException("Unable to save the uploaded image.");
    }

    return 'assets/images/menu/' . $filename;
}

function adminDeleteImage($image) {
    if (
        !$image ||
        !str_starts_with($image, 'assets/images/menu/')
    ) {
        return;
    }

    $filename = basename($image);
    $path = __DIR__ . '/../assets/images/menu/' . $filename;

    if (is_file($path)) {
        unlink($path);
    }
}

function adminUploadCategoryImage($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Category image upload failed.");
    }

    if ($file['size'] > 3 * 1024 * 1024) {
        throw new RuntimeException("Image size must not exceed 3 MB.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException("Only JPG, PNG and WEBP images are allowed.");
    }

    $directory = __DIR__ . '/../assets/images/categories/';

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException("Unable to create category image directory.");
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($file['tmp_name'], $directory . $filename)) {
        throw new RuntimeException("Unable to save category image.");
    }

    return 'assets/images/categories/' . $filename;
}

function adminDeleteCategoryImage($image) {
    if (!$image || !str_starts_with($image, 'assets/images/categories/')) {
        return;
    }

    $filename = basename($image);
    $path = __DIR__ . '/../assets/images/categories/' . $filename;

    if (is_file($path)) {
        unlink($path);
    }
}

