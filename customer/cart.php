
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$cartQuery = $pdo->prepare("
    SELECT c.cart_id, c.quantity, m.item_id, m.name, m.price, m.image, m.availability
    FROM cart c
    INNER JOIN menu_items m ON c.item_id = m.item_id
    WHERE c.user_id = ?
    ORDER BY c.cart_id DESC
");

$cartQuery->execute([$_SESSION['user_id']]);
$cartItems = $cartQuery->fetchAll(PDO::FETCH_ASSOC);

$total = 0;

$foodImages = [
    'burger.jpg' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=700',
    'cheese-burger.jpg' => 'https://images.unsplash.com/photo-1553979459-d2229ba7433a?w=700',
    'margherita.jpg' => 'https://images.unsplash.com/photo-1579751626657-72bc17010498?w=700',
    'farmhouse.jpg' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=700',
    'paneer.jpg' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=700',
    'biryani.jpg' => 'https://images.unsplash.com/photo-1563379091339-03246963d51a?w=700',
    'noodles.jpg' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=700',
    'manchurian.jpg' => 'https://images.unsplash.com/photo-1626804475297-41608ea09aeb?w=700',
    'brownie.jpg' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=700',
    'ice-cream.jpg' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=700',
    'coffee.jpg' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=700',
    'lime-soda.jpg' => 'https://images.unsplash.com/photo-1513558161293-cdaf765edfd4?w=700'
];
?>

<section class="cart-section section-padding">
    <div class="container">
        <div class="cart-heading">
            <span class="section-eyebrow">YOUR SELECTION</span>
            <h1>Shopping <span>Cart</span></h1>
            <p>Review your delicious choices before checkout.</p>
        </div>

        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($_SESSION['cart_message']) ?>
            </div>
            <?php unset($_SESSION['cart_message']); ?>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <i class="bi bi-bag-x"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added anything delicious yet.</p>
                <a href="menu.php" class="btn btn-orange">Explore Menu</a>
            </div>
        <?php else: ?>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                                $image = $foodImages[$item['image']] ?? 'https://images.unsplash.com/photo-1547592180-85f173990554?w=700';
                                $subtotal = (float)$item['price'] * (int)$item['quantity'];
                                $total += $subtotal;
                            ?>

                            <div class="cart-item">
                                <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['name']) ?>">

                                <div class="cart-item-info">
                                    <h5><?= htmlspecialchars($item['name']) ?></h5>
                                    <p>₹<?= number_format((float)$item['price'], 2) ?> each</p>

                                    <div class="cart-quantity">
                                        <form method="POST" action="update_cart.php">
                                            <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id'] ?>">
                                            <input type="hidden" name="action" value="decrease">
                                            <button type="submit" <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>−</button>
                                        </form>

                                        <span><?= (int)$item['quantity'] ?></span>

                                        <form method="POST" action="update_cart.php">
                                            <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id'] ?>">
                                            <input type="hidden" name="action" value="increase">
                                            <button type="submit" <?= $item['quantity'] >= 20 ? 'disabled' : '' ?>>+</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="cart-item-right">
                                    <strong>₹<?= number_format($subtotal, 2) ?></strong>
                                    <form method="POST" action="update_cart.php">
                                        <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id'] ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="remove-cart">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4>Order Summary</h4>

                        <div class="summary-line">
                            <span>Subtotal</span>
                            <strong>₹<?= number_format($total, 2) ?></strong>
                        </div>

                        <div class="summary-line">
                            <span>Delivery Fee</span>
                            <strong>₹40.00</strong>
                        </div>

                        <div class="summary-total">
                            <span>Total</span>
                            <strong>₹<?= number_format($total + 40, 2) ?></strong>
                        </div>

                        <a href="checkout.php" class="btn btn-orange w-100 checkout-btn">
                            Proceed to Checkout <i class="bi bi-arrow-right"></i>
                        </a>

                        <a href="menu.php" class="continue-shopping">
                            <i class="bi bi-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
