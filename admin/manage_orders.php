
<?php
require_once "auth.php";
require_once "../config/database.php";

$statusFilter = trim($_GET['status'] ?? '');

$allowedStatuses = [
    'Pending',
    'Preparing',
    'Out for delivery',
    'Delivered',
    'Cancelled'
];

$sql = "SELECT o.order_id, o.total_amount, o.payment_method,
               o.payment_status, o.status, o.order_date,
               u.name AS customer_name, u.phone
        FROM orders o
        INNER JOIN users u ON o.user_id = u.user_id";

$params = [];

if (in_array($statusFilter, $allowedStatuses, true)) {
    $sql .= " WHERE o.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION['admin_success'] ?? '';
$error = $_SESSION['admin_error'] ?? '';

unset($_SESSION['admin_success'], $_SESSION['admin_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | FoodieHub</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">Foodie<span>Hub.</span></div>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="manage_categories.php">Categories</a>
        <a href="manage_orders.php" class="active">Manage Orders</a>
        <a href="manage_users.php">Customers</a>
        <a href="reports.php">Sales Reports</a>
        <a href="../index.php">Visit Website</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div>
                <h1>Manage Orders</h1>
                <p class="welcome-text">Track and update customer orders.</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="admin-alert success-alert"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="admin-alert error-alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="table-container">
            <div class="filter-row">
                <h2 class="table-heading">All Orders (<?= count($orders) ?>)</h2>

                <form method="GET">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Orders</option>
                        <?php foreach ($allowedStatuses as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if ($orders): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Date</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php $statusClass = str_replace(' ', '-', $order['status'] ?? 'Pending'); ?>
                            <tr>
                                <td>#<?= (int)$order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= htmlspecialchars($order['phone'] ?? 'N/A') ?></td>
                                <td>₹<?= number_format((float)$order['total_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                <td><?= htmlspecialchars($order['payment_status'] ?? 'Pending') ?></td>
                                <td>
                                    <span class="status <?= htmlspecialchars($statusClass) ?>">
                                        <?= htmlspecialchars($order['status'] ?? 'Pending') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($order['order_date']))) ?></td>
                                <td>
                                    <a href="order_details.php?id=<?= (int)$order['order_id'] ?>" class="edit-button">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No orders found.</div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
