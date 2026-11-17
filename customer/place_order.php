
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['checkout_token'] ?? '';

if (!$sessionToken || !hash_equals($sessionToken, $token)) {
    exit("Invalid request. Please return to checkout and try again.");
}

$address = trim($_POST['delivery_address'] ?? '');
$paymentMethod = $_POST['payment_method'] ?? '';

if ($address === '' || strlen($address) > 500) {
    exit("Please enter a valid delivery address.");
}

if (!in_array($paymentMethod, ['COD', 'Online'], true)) {
    exit("Invalid payment method.");
}

$paymentMethod = $paymentMethod === 'Online' ? 'ONLINE' : 'COD';

$userId = (int)$_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $cartQuery = $pdo->prepare("
        SELECT c.item_id, c.quantity, m.price, m.availability, m.name
        FROM cart c
        INNER JOIN menu_items m ON c.item_id = m.item_id
        WHERE c.user_id = ?
        FOR UPDATE
    ");

    $cartQuery->execute([$userId]);
    $items = $cartQuery->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        throw new Exception("Your cart is empty.");
    }

    $subtotal = 0;

    foreach ($items as $item) {
        if (!(int)$item['availability']) {
            throw new Exception($item['name'] . " is currently unavailable.");
        }

        if ((int)$item['quantity'] < 1 || (int)$item['quantity'] > 20) {
            throw new Exception("Invalid item quantity.");
        }

        $subtotal += (float)$item['price'] * (int)$item['quantity'];
    }

    $deliveryFee = 40;
    $total = $subtotal + $deliveryFee;

    $paymentStatus = $paymentMethod === 'COD' ? 'Pending' : 'Paid';

    $orderQuery = $pdo->prepare("
        INSERT INTO orders
        (user_id, total_amount, payment_method, status, delivery_address, payment_status, order_date)
        VALUES (?, ?, ?, 'Pending', ?, ?, NOW())
    ");

    $orderQuery->execute([
        $userId,
        $total,
        $paymentMethod,
        $address,
        $paymentStatus
    ]);

    $orderId = (int)$pdo->lastInsertId();

    $orderItemQuery = $pdo->prepare("
        INSERT INTO order_items
        (order_id, item_id, item_name, quantity, price)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $orderItemQuery->execute([
            $orderId,
            $item['item_id'],
            $item['name'],
            $item['quantity'],
            $item['price']
        ]);
    }

    $clearCart = $pdo->prepare("
        DELETE FROM cart WHERE user_id = ?
    ");

    $clearCart->execute([$userId]);

    $pdo->commit();

    unset($_SESSION['checkout_token']);

    $_SESSION['last_order_id'] = $orderId;

    header("Location: order_success.php");
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['checkout_error'] = $e->getMessage();

    header("Location: checkout.php");
    exit;
}
