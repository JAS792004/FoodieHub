
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

$stmt = $pdo->prepare(
    "UPDATE menu_items
     SET availability = CASE WHEN availability = 1 THEN 0 ELSE 1 END
     WHERE item_id = ?"
);

$stmt->execute([$itemId]);

$_SESSION['admin_success'] = $stmt->rowCount()
    ? "Food item availability updated."
    : "No change was made to the food item.";

header("Location: manage_menu.php");
exit;
