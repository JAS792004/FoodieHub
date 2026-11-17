
<?php
require_once "auth.php";
require_once "../config/database.php";

$error = "";

$categories = $pdo->query(
    "SELECT category_id, category_name FROM categories ORDER BY category_name"
)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminVerifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = filter_var($_POST['category_id'] ?? '', FILTER_VALIDATE_INT);
    $price = $_POST['price'] ?? '';

    if ($name === '' || strlen($name) > 150) {
        $error = "Please enter a valid food item name.";
    } elseif (!$categoryId) {
        $error = "Please select a category.";
    } elseif (!is_numeric($price) || (float)$price <= 0 || (float)$price > 99999999) {
        $error = "Please enter a valid price.";
    } elseif (strlen($description) > 65535) {
        $error = "Description is too long.";
    } else {
        $check = $pdo->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $check->execute([$categoryId]);

        if (!$check->fetch()) {
            $error = "Selected category does not exist.";
        } else {
            $image = null;

            try {
                $image = adminUploadImage($_FILES['image'] ?? null);

                $stmt = $pdo->prepare(
                    "INSERT INTO menu_items
                    (category_id, name, description, price, image, availability)
                    VALUES (?, ?, ?, ?, ?, 1)"
                );

                $stmt->execute([
                    $categoryId,
                    $name,
                    $description,
                    number_format((float)$price, 2, '.', ''),
                    $image
                ]);

                $_SESSION['admin_success'] = "Food item added successfully.";
                header("Location: manage_menu.php");
                exit;

            } catch (Throwable $e) {
                if ($image) {
                    adminDeleteImage($image);
                }

                $error = $e instanceof RuntimeException
                    ? $e->getMessage()
                    : "Unable to add the food item. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Food Item | FoodieHub</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

<div class="dashboard-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">Foodie<span>Hub.</span></div>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_menu.php" class="active">Manage Menu</a>
        <a href="manage_categories.php">Categories</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_users.php">Customers</a>
        <a href="reports.php">Sales Reports</a>
        <a href="../index.php">Visit Website</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Add Food Item</h1>
                <p class="welcome-text">Enter the details of the new menu item.</p>
            </div>
            <a href="manage_menu.php" class="secondary-link">Back to Menu</a>
        </div>

        <section class="admin-form-container">

            <?php if ($error): ?>
                <div class="admin-alert error-alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!$categories): ?>
                <div class="admin-alert error-alert">
                    No categories exist. Please create a category before adding a food item.
                </div>
                <a href="manage_categories.php" class="primary-link">Manage Categories</a>
            <?php else: ?>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">

                    <label>Food Item Name</label>
                    <input type="text" name="name" maxlength="150" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= (int)$category['category_id'] ?>"
                                <?= (($_POST['category_id'] ?? '') == $category['category_id']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Description</label>
                    <textarea name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

                    <label>Price (₹)</label>
                    <input type="number" name="price" min="0.01" max="99999999" step="0.01" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">

                    <label>Food Image</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

                    <p class="form-hint">Allowed formats: JPG, PNG and WEBP. Maximum size: 3 MB.</p>

                    <button type="submit" class="primary-button">Add Food Item</button>
                </form>

            <?php endif; ?>

        </section>

    </main>
</div>

</body>
</html>
