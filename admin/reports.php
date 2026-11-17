
<?php
require_once "auth.php";
require_once "../config/database.php";

$today = date('Y-m-d');

$fromDate = $_GET['from'] ?? date('Y-m-01');
$toDate = $_GET['to'] ?? $today;

$validDates = (
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) &&
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate) &&
    strtotime($fromDate) !== false &&
    strtotime($toDate) !== false &&
    $fromDate <= $toDate &&
    $toDate <= $today
);

$error = '';

if (!$validDates) {
    $fromDate = date('Y-m-01');
    $toDate = $today;
    $error = "Please select a valid date range.";
}

$startDateTime = $fromDate . ' 00:00:00';
$endDateTime = date('Y-m-d', strtotime($toDate . ' +1 day')) . ' 00:00:00';

$summaryStmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END), 0) AS paid_revenue,
        COALESCE(SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END), 0) AS pending_payments,
        COALESCE(AVG(CASE WHEN payment_status = 'Paid' THEN total_amount END), 0) AS average_order
     FROM orders
     WHERE order_date >= ? AND order_date < ?"
);

$summaryStmt->execute([$startDateTime, $endDateTime]);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

$paymentStmt = $pdo->prepare(
    "SELECT payment_method,
            COUNT(*) AS order_count,
            COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END), 0) AS paid_amount
     FROM orders
     WHERE order_date >= ? AND order_date < ?
     GROUP BY payment_method"
);

$paymentStmt->execute([$startDateTime, $endDateTime]);
$paymentMethods = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

$statusStmt = $pdo->prepare(
    "SELECT status, COUNT(*) AS order_count
     FROM orders
     WHERE order_date >= ? AND order_date < ?
     GROUP BY status
     ORDER BY order_count DESC"
);

$statusStmt->execute([$startDateTime, $endDateTime]);
$orderStatuses = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

$dailyStmt = $pdo->prepare(
    "SELECT DATE(order_date) AS report_date,
            COUNT(*) AS order_count,
            COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END), 0) AS revenue
     FROM orders
     WHERE order_date >= ? AND order_date < ?
     GROUP BY DATE(order_date)
     ORDER BY report_date DESC"
);

$dailyStmt->execute([$startDateTime, $endDateTime]);
$dailyReports = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports | FoodieHub</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">Foodie<span>Hub.</span></div>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="manage_categories.php">Categories</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_users.php">Customers</a>
        <a href="reports.php" class="active">Sales Reports</a>
        <a href="../index.php">Visit Website</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div>
                <h1>Sales Reports</h1>
                <p class="welcome-text">Analyse your restaurant's sales and orders.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="admin-alert error-alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="table-container report-filter">
            <form method="GET" class="date-filter">
                <div>
                    <label>From Date</label>
                    <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>" max="<?= htmlspecialchars($today) ?>" required>
                </div>

                <div>
                    <label>To Date</label>
                    <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>" max="<?= htmlspecialchars($today) ?>" required>
                </div>

                <button type="submit" class="primary-button">Generate Report</button>
            </form>
        </section>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <p>Total Orders</p>
                <h2><?= number_format((int)$summary['total_orders']) ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <p>Paid Revenue</p>
                <h2>₹<?= number_format((float)$summary['paid_revenue'], 2) ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <p>Pending Payments</p>
                <h2><?= number_format((int)$summary['pending_payments']) ?></h2>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🧾</div>
                <p>Average Paid Order</p>
                <h2>₹<?= number_format((float)$summary['average_order'], 2) ?></h2>
            </div>
        </div>

        <section class="table-container report-section">
            <h2 class="table-heading">Payment Method Report</h2>

            <?php if ($paymentMethods): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th>Number of Orders</th>
                            <th>Paid Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentMethods as $method): ?>
                            <tr>
                                <td><?= htmlspecialchars($method['payment_method']) ?></td>
                                <td><?= (int)$method['order_count'] ?></td>
                                <td>₹<?= number_format((float)$method['paid_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No payment data available for this period.</div>
            <?php endif; ?>
        </section>

        <section class="table-container report-section">
            <h2 class="table-heading">Order Status Report</h2>

            <?php if ($orderStatuses): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order Status</th>
                            <th>Number of Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderStatuses as $status): ?>
                            <tr>
                                <td><?= htmlspecialchars($status['status'] ?? 'Pending') ?></td>
                                <td><?= (int)$status['order_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No order data available for this period.</div>
            <?php endif; ?>
        </section>

        <section class="table-container report-section">
            <h2 class="table-heading">Daily Sales Report</h2>

            <?php if ($dailyReports): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Orders</th>
                            <th>Paid Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailyReports as $day): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($day['report_date']))) ?></td>
                                <td><?= (int)$day['order_count'] ?></td>
                                <td>₹<?= number_format((float)$day['revenue'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No daily sales data available.</div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
