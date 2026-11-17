
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_order_id'])) {
    header("Location: ../index.php");
    exit;
}

$orderId = (int)$_SESSION['last_order_id'];

$orderQuery = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE order_id = ? AND user_id = ?
");

$orderQuery->execute([$orderId, $_SESSION['user_id']]);
$order = $orderQuery->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    unset($_SESSION['last_order_id']);
    header("Location: orders.php");
    exit;
}
?>

<section class="order-success-section section-padding">
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>

            <span class="section-eyebrow">ORDER CONFIRMED</span>
            <h1>Thank You for <span>Your Order!</span></h1>

            <p>Your order has been successfully placed. We're getting it ready for you.</p>

            <div class="order-confirmation-details">
                <div>
                    <span>Order Number</span>
                    <strong>#<?= $orderId ?></strong>
                </div>

                <div>
                    <span>Order Amount</span>
                    <strong>₹<?= number_format((float)$order['total_amount'], 2) ?></strong>
                </div>

                <div>
                    <span>Payment Method</span>
                    <strong><?= htmlspecialchars($order['payment_method']) ?></strong>
                </div>

                <div>
                    <span>Payment Status</span>
                    <strong><?= htmlspecialchars($order['payment_status']) ?></strong>
                </div>

                <div>
                    <span>Order Status</span>
                    <strong><?= htmlspecialchars($order['status']) ?></strong>
                </div>
            </div>

            <div class="success-actions">
                <a href="orders.php" class="btn btn-orange">Track My Order</a>
                <a href="menu.php" class="btn btn-outline-dark">Continue Shopping</a>
            </div>
        </div>
    </div>
</section>

<?php
unset($_SESSION['last_order_id']);
require_once '../includes/footer.php';
?>
