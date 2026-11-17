
<?php
require_once "auth.php";
require_once "../config/database.php";

$search = trim($_GET['search'] ?? '');

$sql = "SELECT u.user_id, u.name, u.email, u.phone, u.address, u.created_at,
               COUNT(o.order_id) AS total_orders,
               COALESCE(SUM(
                   CASE WHEN o.payment_status = 'Paid' THEN o.total_amount ELSE 0 END
               ), 0) AS paid_total
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id
        WHERE u.role = 'customer'";

$params = [];

if ($search !== '') {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $term = '%' . $search . '%';
    $params = [$term, $term, $term];
}

$sql .= " GROUP BY u.user_id, u.name, u.email, u.phone, u.address, u.created_at
          ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalCustomers = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'customer'"
)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | FoodieHub</title>
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
        <a href="manage_users.php" class="active">Customers</a>
        <a href="reports.php">Sales Reports</a>
        <a href="../index.php">Visit Website</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div>
                <h1>Customers</h1>
                <p class="welcome-text">View registered FoodieHub customers.</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <p>Total Registered Customers</p>
                <h2><?= number_format((int)$totalCustomers) ?></h2>
            </div>
        </div>

        <section class="table-container">
            <div class="filter-row">
                <h2 class="table-heading">Customer Directory</h2>

                <form method="GET" class="search-form">
                    <input
                        type="search"
                        name="search"
                        placeholder="Search name, email or phone"
                        value="<?= htmlspecialchars($search) ?>"
                    >
                    <button type="submit" class="primary-button">Search</button>
                    <?php if ($search !== ''): ?>
                        <a href="manage_users.php" class="secondary-link">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($customers): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Orders</th>
                            <th>Paid Total</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($customer['name']) ?></strong></td>
                                <td><?= htmlspecialchars($customer['email']) ?></td>
                                <td><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($customer['address'] ?? 'N/A') ?></td>
                                <td><?= (int)$customer['total_orders'] ?></td>
                                <td>₹<?= number_format((float)$customer['paid_total'], 2) ?></td>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($customer['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No customers found.</div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
