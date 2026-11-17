
<?php
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userQuery = $pdo->prepare("
    SELECT name, email, phone, address
    FROM users
    WHERE user_id = ?
");

$userQuery->execute([$_SESSION['user_id']]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ../logout.php");
    exit;
}

$cartQuery = $pdo->prepare("
    SELECT c.cart_id, c.quantity, m.item_id, m.name, m.price, m.availability
    FROM cart c
    INNER JOIN menu_items m ON c.item_id = m.item_id
    WHERE c.user_id = ?
");

$cartQuery->execute([$_SESSION['user_id']]);
$cartItems = $cartQuery->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

$subtotal = 0;

foreach ($cartItems as $item) {
    if (!(int)$item['availability']) {
        $_SESSION['cart_message'] = "One or more items in your cart are unavailable.";
        header("Location: cart.php");
        exit;
    }

    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

$deliveryFee = 40;
$total = $subtotal + $deliveryFee;

$_SESSION['checkout_token'] = bin2hex(random_bytes(32));

require_once '../includes/header.php';
?>

<section class="checkout-section section-padding">
    <div class="container">
        <div class="checkout-heading">
            <?php if (isset($_SESSION['checkout_error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['checkout_error']) ?>
    </div>
    <?php unset($_SESSION['checkout_error']); ?>
<?php endif; ?>
            <span class="section-eyebrow">ALMOST THERE</span>
            <h1>Complete Your <span>Order</span></h1>
            <p>Just a few more details before your delicious meal arrives.</p>
        </div>

        <form action="place_order.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['checkout_token']) ?>">

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="checkout-card">
                        <div class="checkout-card-heading">
                            <span class="checkout-number">01</span>
                            <div>
                                <h4>Delivery Details</h4>
                                <p>Where should we deliver your order?</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Delivery Address</label>
                            <textarea name="delivery_address" class="form-control" rows="4" placeholder="Enter your complete delivery address" required maxlength="500"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="checkout-card mt-4">
                        <div class="checkout-card-heading">
                            <span class="checkout-number">02</span>
                            <div>
                                <h4>Payment Method</h4>
                                <p>Select how you would like to pay.</p>
                            </div>
                        </div>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="COD" checked>
                            <span class="payment-icon"><i class="bi bi-cash-stack"></i></span>
                            <span class="payment-info">
                                <strong>Cash on Delivery</strong>
                                <small>Pay when your order arrives.</small>
                            </span>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="Online">
                            <span class="payment-icon"><i class="bi bi-credit-card"></i></span>
                            <span class="payment-info">
                                <strong>Online Payment</strong>
                                <small>Simulated payment for project demonstration.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="checkout-summary">
                        <h4>Your Order</h4>

                        <?php foreach ($cartItems as $item): ?>
                            <div class="checkout-item">
                                <div>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <small>Quantity: <?= (int)$item['quantity'] ?></small>
                                </div>
                                <strong>₹<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?></strong>
                            </div>
                        <?php endforeach; ?>

                        <div class="checkout-divider"></div>

                        <div class="summary-line">
                            <span>Subtotal</span>
                            <strong>₹<?= number_format($subtotal, 2) ?></strong>
                        </div>

                        <div class="summary-line">
                            <span>Delivery Fee</span>
                            <strong>₹<?= number_format($deliveryFee, 2) ?></strong>
                        </div>

                        <div class="summary-total">
                            <span>Grand Total</span>
                            <strong>₹<?= number_format($total, 2) ?></strong>
                        </div>

                        <button type="submit" class="btn btn-orange w-100 checkout-btn">
                            Place Order <i class="bi bi-arrow-right"></i>
                        </button>

                        <p class="checkout-security">
                            <i class="bi bi-shield-check"></i>
                            Your order information is securely processed.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
