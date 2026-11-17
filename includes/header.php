
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$basePath = str_contains($_SERVER['SCRIPT_NAME'], '/customer/') || str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? '../' : '';

$cartCount = 0;

if (isset($_SESSION['user_id'])) {
    $cartQuery = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
    $cartQuery->execute([$_SESSION['user_id']]);
    $cartCount = (int) $cartQuery->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodieHub - Delicious Food Delivered</title>
    <meta name="description" content="Order delicious food online with FoodieHub.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css">
</head>
<body>
<?php require __DIR__ . '/navbar.php'; ?>
