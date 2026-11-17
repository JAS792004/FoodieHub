
<?php
require_once "auth.php";
require_once "../config/database.php";

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId) {
    header("Location: manage_orders.php");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT o.*, u.name AS customer_name, u.email, u.phone
     FROM orders o
     INNER JOIN users u ON o.user_id = u.user_id
     WHERE o.order_id = ?"
);

$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: manage_orders.php");
    exit;
}

$itemStmt = $pdo->prepare(
    "SELECT id, item_id, item_name, quantity, price
     FROM order_items
     WHERE order_id = ?"
);

$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION['admin_success'] ?? '';
$error = $_SESSION['admin_error'] ?? '';

unset($_SESSION['admin_success'], $_SESSION['admin_error']);

$orderStatuses = [
    'Pending',
    'Preparing',
    'Out for delivery',
    'Delivered',
    'Cancelled'
];

$paymentStatuses = [
    'Pending',
    'Paid',
    'Failed'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= (int)$orderId ?> | FoodieHub</title>
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
                <h1>Order #<?= (int)$order['order_id'] ?></h1>
                <p class="welcome-text">Review order information and update its status.</p>
            </div>
            <a href="manage_orders.php" class="secondary-link">Back to Orders</a>
        </div>

        <?php if ($success): ?>
            <div class="admin-alert success-alert"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="admin-alert error-alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="order-details-grid">
            <section class="admin-form-container">
                <h2 class="table-heading">Customer Information</h2>
                <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
                <p><strong>Delivery Address:</strong></p>
                <p><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
                <p><strong>Order Date:</strong> <?= htmlspecialchars(date('d M Y, h:i A', strtotime($order['order_date']))) ?></p>
            </section>

            <section class="admin-form-container">
                <h2 class="table-heading">Update Order</h2>

                <form action="update_order.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                    <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">

                    <label>Order Status</label>
                    <select name="status" required>
                        <?php foreach ($orderStatuses as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Payment Status</label>
                    <select name="payment_status" required>
                        <?php foreach ($paymentStatuses as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= $order['payment_status'] === $status ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="primary-button">Update Order</button>
                </form>
            </section>
        </div>

        <section class="table-container order-items-section">
            <h2 class="table-heading">Ordered Items</h2>

            <?php if ($items): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Food Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td><?= (int)$item['quantity'] ?></td>
                                <td>₹<?= number_format((float)$item['price'], 2) ?></td>
                                <td>₹<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No order items found.</div>
            <?php endif; ?>

            <div class="order-total">
                <p>Payment Method: <strong><?= htmlspecialchars($order['payment_method']) ?></strong></p>
                <p>Payment Status: <strong><?= htmlspecialchars($order['payment_status'] ?? 'Pending') ?></strong></p>
                <h2>Total: ₹<?= number_format((float)$order['total_amount'], 2) ?></h2>
            </div>
        </section>
    </main>
</div>
</body>
</html>
