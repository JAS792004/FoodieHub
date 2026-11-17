
<?php
require_once "auth.php";
require_once "../config/database.php";

$stmt = $pdo->query(
    "SELECT m.item_id, m.name, m.description, m.price, m.image,
            m.availability, c.category_name
     FROM menu_items m
     INNER JOIN categories c ON m.category_id = c.category_id
     ORDER BY m.item_id DESC"
);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION['admin_success'] ?? '';
$error = $_SESSION['admin_error'] ?? '';

unset($_SESSION['admin_success'], $_SESSION['admin_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu | FoodieHub</title>
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
                <h1>Manage Menu</h1>
                <p class="welcome-text">Add, update and manage your food items.</p>
            </div>

            <a href="add_item.php" class="primary-link">+ Add New Item</a>
        </div>

        <?php if ($success): ?>
            <div class="admin-alert success-alert">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="admin-alert error-alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <section class="table-container">
            <h2 class="table-heading">All Menu Items (<?= count($items) ?>)</h2>

            <?php if ($items): ?>

                <table class="menu-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Availability</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img
                                            src="../<?= htmlspecialchars($item['image']) ?>"
                                            class="menu-thumbnail"
                                            alt="<?= htmlspecialchars($item['name']) ?>"
                                        >
                                    <?php else: ?>
                                        <div class="no-image">No Image</div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <div class="item-description">
                                        <?= htmlspecialchars($item['description'] ?? '') ?>
                                    </div>
                                </td>

                                <td><?= htmlspecialchars($item['category_name']) ?></td>

                                <td>₹<?= number_format((float)$item['price'], 2) ?></td>

                                <td>
                                    <span class="availability <?= $item['availability'] ? 'available' : 'unavailable' ?>">
                                        <?= $item['availability'] ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="action-buttons">

                                        <a
                                            href="edit_item.php?id=<?= (int)$item['item_id'] ?>"
                                            class="edit-button"
                                        >Edit</a>

                                        <form action="toggle_availability.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                            <input type="hidden" name="item_id" value="<?= (int)$item['item_id'] ?>">
                                            <button type="submit" class="toggle-button">
                                                <?= $item['availability'] ? 'Disable' : 'Enable' ?>
                                            </button>
                                        </form>

                                        <form
                                            action="delete_item.php"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this food item?')"
                                        >
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                            <input type="hidden" name="item_id" value="<?= (int)$item['item_id'] ?>">
                                            <button type="submit" class="delete-button">Delete</button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <div class="empty-message">No food items found. Add your first menu item.</div>
            <?php endif; ?>

        </section>

    </main>
</div>

</body>
</html>
