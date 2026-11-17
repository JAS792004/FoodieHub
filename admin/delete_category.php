
<?php
require_once "auth.php";
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_categories.php");
    exit;
}

adminVerifyCsrf();

$categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

if (!$categoryId) {
    $_SESSION['admin_error'] = "Invalid category.";
    header("Location: manage_categories.php");
    exit;
}

$stmt = $pdo->prepare("SELECT image FROM categories WHERE category_id = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    $_SESSION['admin_error'] = "Category not found.";
    header("Location: manage_categories.php");
    exit;
}

$count = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE category_id = ?");
$count->execute([$categoryId]);

if ((int)$count->fetchColumn() > 0) {
    $_SESSION['admin_error'] = "This category contains food items. Move or delete those items before deleting the category.";
} else {
    try {
        $delete = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $delete->execute([$categoryId]);

        if ($delete->rowCount()) {
            adminDeleteCategoryImage($category['image']);
            $_SESSION['admin_success'] = "Category deleted successfully.";
        } else {
            $_SESSION['admin_error'] = "Category could not be deleted.";
        }
    } catch (PDOException $e) {
        $_SESSION['admin_error'] = "Unable to delete this category.";
    }
}

header("Location: manage_categories.php");
exit;
