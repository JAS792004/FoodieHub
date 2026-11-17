
<?php
require_once "auth.php";
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_menu.php");
    exit;
}

adminVerifyCsrf();

$itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);

if (!$itemId) {
    $_SESSION['admin_error'] = "Invalid food item.";
    header("Location: manage_menu.php");
    exit;
}

$stmt = $pdo->prepare("SELECT image FROM menu_items WHERE item_id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    $_SESSION['admin_error'] = "Food item not found.";
    header("Location: manage_menu.php");
    exit;
}

try {
    $delete = $pdo->prepare("DELETE FROM menu_items WHERE item_id = ?");
    $delete->execute([$itemId]);

    if ($delete->rowCount()) {
        adminDeleteImage($item['image']);
        $_SESSION['admin_success'] = "Food item deleted successfully.";
    } else {
        $_SESSION['admin_error'] = "Unable to delete the food item.";
    }

} catch (PDOException $e) {
    $_SESSION['admin_error'] = "This item may be linked to an existing order and cannot be deleted. Mark it unavailable instead.";
}

header("Location: manage_menu.php");
exit;
