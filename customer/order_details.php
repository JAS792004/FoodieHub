
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId) {
    header("Location: orders.php");
    exit;
}

$orderQuery = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE order_id = ? AND user_id = ?
");

$orderQuery->execute([$orderId, $_SESSION['user_id']]);
$order = $orderQuery->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: orders.php");
    exit;
}

$itemsQuery = $pdo->prepare("
    SELECT oi.quantity, oi.price, m.name
    FROM order_items oi
    INNER JOIN menu_items m ON oi.item_id = m.item_id
    WHERE oi.order_id = ?
");

$itemsQuery->execute([$orderId]);
$items = $itemsQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="orders-section section-padding">
    <div class="container">
        <a href="orders.php" class="continue-shopping">
            <i class="bi bi-arrow-left"></i> Back to My Orders
        </a>

        <div class="orders-heading mt-4">
            <span class="section-eyebrow">ORDER INFORMATION</span>
            <h1>Order <span>#<?= $orderId ?></span></h1>
            <p>Here are the details of your order.</p>
        </div>

        <div class="order-card">
            <div class="order-card-top">
                <div>
                    <span class="order-label">ORDER STATUS</span>
                    <h4><?= htmlspecialchars($order['status']) ?></h4>
                </div>
                <strong>₹<?= number_format((float)$order['total_amount'], 2) ?></strong>
            </div>

            <div class="checkout-divider"></div>

            <h5>Ordered Items</h5>

            <?php foreach ($items as $item): ?>
                <div class="checkout-item">
                    <div>
                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                        <small>Quantity: <?= (int)$item['quantity'] ?></small>
                    </div>
                    <strong>₹<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?></strong>
                </div>
            <?php endforeach; ?>

            <div class="checkout-divider"></div>

            <h5>Delivery Address</h5>
            <p><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>

            <div class="order-card-bottom">
                <div>
                    <span>Payment Method</span>
                    <strong><?= htmlspecialchars($order['payment_method']) ?></strong>
                </div>

                <div>
                    <span>Payment Status</span>
                    <strong><?= htmlspecialchars($order['payment_status']) ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
