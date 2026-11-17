
<?php
require_once "auth.php";
require_once "../config/database.php";

$categoryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$categoryId) {
    header("Location: manage_categories.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: manage_categories.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminVerifyCsrf();

    $name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || strlen($name) > 100) {
        $error = "Enter a valid category name.";
    } elseif (strlen($description) > 65535) {
        $error = "Description is too long.";
    } else {
        $newImage = null;

        try {
            $newImage = adminUploadCategoryImage($_FILES['image'] ?? null);
            $image = $newImage ?: $category['image'];

            $update = $pdo->prepare(
                "UPDATE categories
                 SET category_name = ?, description = ?, image = ?
                 WHERE category_id = ?"
            );

            $update->execute([$name, $description, $image, $categoryId]);

            if ($newImage && $category['image']) {
                adminDeleteCategoryImage($category['image']);
            }

            $_SESSION['admin_success'] = "Category updated successfully.";
            header("Location: manage_categories.php");
            exit;
        } catch (Throwable $e) {
            if ($newImage) {
                adminDeleteCategoryImage($newImage);
            }

            $error = $e instanceof RuntimeException
                ? $e->getMessage()
                : "Unable to update category. The name may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category | FoodieHub</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">Foodie<span>Hub.</span></div>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="manage_categories.php" class="active">Categories</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_users.php">Customers</a>
        <a href="reports.php">Sales Reports</a>
        <a href="../index.php">Visit Website</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div>
                <h1>Edit Category</h1>
                <p class="welcome-text">Update category information.</p>
            </div>
            <a href="manage_categories.php" class="secondary-link">Back to Categories</a>
        </div>

        <section class="admin-form-container">
            <?php if ($error): ?>
                <div class="admin-alert error-alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">

                <label>Category Name</label>
                <input type="text" name="category_name" maxlength="100" required value="<?= htmlspecialchars($_POST['category_name'] ?? $category['category_name']) ?>">

                <label>Description</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? $category['description'] ?? '') ?></textarea>

                <?php if ($category['image']): ?>
                    <label>Current Image</label>
                    <img src="../<?= htmlspecialchars($category['image']) ?>" class="edit-image-preview" alt="Category image">
                <?php endif; ?>

                <label>Replace Image</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

                <p class="form-hint">Leave empty to keep the existing image.</p>

                <button type="submit" class="primary-button">Save Changes</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
