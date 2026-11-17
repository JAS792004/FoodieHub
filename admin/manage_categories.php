
<?php
require_once "auth.php";
require_once "../config/database.php";

$stmt = $pdo->query(
    "SELECT c.category_id, c.category_name, c.description, c.image,
            COUNT(m.item_id) AS item_count
     FROM categories c
     LEFT JOIN menu_items m ON c.category_id = m.category_id
     GROUP BY c.category_id, c.category_name, c.description, c.image
     ORDER BY c.category_id DESC"
);

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION['admin_success'] ?? '';
$error = $_SESSION['admin_error'] ?? '';

unset($_SESSION['admin_success'], $_SESSION['admin_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | FoodieHub</title>
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
                <h1>Manage Categories</h1>
                <p class="welcome-text">Organise your restaurant menu.</p>
            </div>
            <a href="add_category.php" class="primary-link">+ Add Category</a>
        </div>

        <?php if ($success): ?>
            <div class="admin-alert success-alert"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="admin-alert error-alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="table-container">
            <h2 class="table-heading">All Categories (<?= count($categories) ?>)</h2>

            <?php if ($categories): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Menu Items</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <?php if ($category['image']): ?>
                                        <img
                                            src="../<?= htmlspecialchars($category['image']) ?>"
                                            class="menu-thumbnail"
                                            alt="<?= htmlspecialchars($category['category_name']) ?>"
                                        >
                                    <?php else: ?>
                                        <div class="no-image">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($category['category_name']) ?></strong></td>
                                <td><?= htmlspecialchars($category['description'] ?? '') ?></td>
                                <td><?= (int)$category['item_count'] ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_category.php?id=<?= (int)$category['category_id'] ?>" class="edit-button">Edit</a>

                                        <form action="delete_category.php" method="POST" onsubmit="return confirm('Delete this category?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                            <input type="hidden" name="category_id" value="<?= (int)$category['category_id'] ?>">
                                            <button type="submit" class="delete-button">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No categories found.</div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
