
<?php
require_once "auth.php";
require_once "../config/database.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminVerifyCsrf();

    $name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image = null;

    if ($name === '' || strlen($name) > 100) {
        $error = "Enter a category name of up to 100 characters.";
    } elseif (strlen($description) > 65535) {
        $error = "Description is too long.";
    } else {
        try {
            $image = adminUploadCategoryImage($_FILES['image'] ?? null);

            $stmt = $pdo->prepare(
                "INSERT INTO categories (category_name, description, image)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([$name, $description, $image]);

            $_SESSION['admin_success'] = "Category added successfully.";
            header("Location: manage_categories.php");
            exit;
        } catch (Throwable $e) {
            if ($image) {
                adminDeleteCategoryImage($image);
            }

            $error = $e instanceof RuntimeException
                ? $e->getMessage()
                : "Unable to add category. The category name may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category | FoodieHub</title>
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
                <h1>Add Category</h1>
                <p class="welcome-text">Create a new menu category.</p>
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
                <input type="text" name="category_name" maxlength="100" required value="<?= htmlspecialchars($_POST['category_name'] ?? '') ?>">

                <label>Description</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

                <label>Category Image</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

                <p class="form-hint">JPG, PNG or WEBP. Maximum 3 MB.</p>

                <button type="submit" class="primary-button">Add Category</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
