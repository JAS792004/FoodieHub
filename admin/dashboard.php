
<?php
require_once "auth.php";
require_once "../config/database.php";

function getCount($pdo, $sql) {
    return (int) $pdo->query($sql)->fetchColumn();
}

$totalOrders = getCount($pdo, "SELECT COUNT(*) FROM orders");
$totalCustomers = getCount($pdo, "SELECT COUNT(*) FROM users WHERE role = 'customer'");
$totalItems = getCount($pdo, "SELECT COUNT(*) FROM menu_items");
$totalCategories = getCount($pdo, "SELECT COUNT(*) FROM categories");

$paidRevenue = $pdo->query(
    "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Paid'"
)->fetchColumn();

$stmt = $pdo->query(
    "SELECT o.order_id, o.total_amount, o.payment_method, o.payment_status,
            o.status, o.order_date, u.name AS customer_name
     FROM orders o
     INNER JOIN users u ON o.user_id = u.user_id
     ORDER BY o.order_date DESC
     LIMIT 10"
);

$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | FoodieHub</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

<div class="dashboard-layout">

    <aside class="sidebar">
        <div class="sidebar-logo">Foodie<span>Hub.</span></div>

        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="manage_menu.php">Manage Menu</a>
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
                <h1>Dashboard</h1>
                <p class="welcome-text">
                    Welcome back, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>!
                    Here's your restaurant overview.
                </p>
            </div>

            <a href="logout.php" class="logout-link">Logout</a>
        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <p>Total Orders</p>
                <h2><?= number_format($totalOrders) ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <p>Total Customers</p>
                <h2><?= number_format($totalCustomers) ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🍔</div>
                <p>Menu Items</p>
                <h2><?= number_format($totalItems) ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📂</div>
                <p>Categories</p>
                <h2><?= number_format($totalCategories) ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <p>Paid Revenue</p>
                <h2>₹<?= number_format((float)$paidRevenue, 2) ?></h2>
            </div>

        </div>

        <section class="table-container">
            <h2 class="table-heading">Recent Orders</h2>

            <?php if (count($recentOrders) > 0): ?>

                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <?php
                                $statusClass = str_replace(
                                    ' ',
                                    '-',
                                    $order['status'] ?? 'Pending'
                                );
                            ?>

                            <tr>
                                <td>#<?= (int)$order['order_id'] ?></td>

                                <td>
                                    <?= htmlspecialchars($order['customer_name']) ?>
                                </td>

                                <td>
                                    ₹<?= number_format((float)$order['total_amount'], 2) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($order['payment_method']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($order['payment_status'] ?? 'Pending') ?>
                                </td>

                                <td>
                                    <span class="status <?= htmlspecialchars($statusClass) ?>">
                                        <?= htmlspecialchars($order['status'] ?? 'Pending') ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars(date('d M Y', strtotime($order['order_date']))) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else: ?>

                <div class="empty-message">
                    No orders have been placed yet.
                </div>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>
