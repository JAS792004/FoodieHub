
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$orderQuery = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY order_date DESC
");

$orderQuery->execute([$_SESSION['user_id']]);
$orders = $orderQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="orders-section section-padding">
    <div class="container">
        <div class="orders-heading">
            <span class="section-eyebrow">YOUR FOOD JOURNEY</span>
            <h1>My <span>Orders</span></h1>
            <p>Keep track of all your delicious orders.</p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-cart">
                <i class="bi bi-receipt"></i>
                <h3>No orders yet</h3>
                <p>Your previous orders will appear here.</p>
                <a href="menu.php" class="btn btn-orange">Explore Menu</a>
            </div>
        <?php else: ?>

            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <?php
                        $statusClass = match ($order['status']) {
                            'Delivered' => 'status-delivered',
                            'Cancelled' => 'status-cancelled',
                            'Preparing' => 'status-preparing',
                            'Out for delivery' => 'status-transit',
                            default => 'status-pending'
                        };
                    ?>

                    <div class="order-card">
                        <div class="order-card-top">
                            <div>
                                <span class="order-label">ORDER NUMBER</span>
                                <h4>#<?= (int)$order['order_id'] ?></h4>
                                <small><?= htmlspecialchars($order['order_date']) ?></small>
                            </div>

                            <span class="order-status <?= $statusClass ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </div>

                        <div class="order-card-bottom">
                            <div>
                                <span>Total Amount</span>
                                <strong>₹<?= number_format((float)$order['total_amount'], 2) ?></strong>
                            </div>

                            <div>
                                <span>Payment</span>
                                <strong><?= htmlspecialchars($order['payment_method']) ?></strong>
                            </div>

                            <div>
                                <span>Payment Status</span>
                                <strong><?= htmlspecialchars($order['payment_status']) ?></strong>
                            </div>
                        </div>

                        <a href="order_details.php?id=<?= (int)$order['order_id'] ?>" class="order-details-link">
                            View Order Details <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
