
<?php
require_once "auth.php";
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_orders.php");
    exit;
}

adminVerifyCsrf();

$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';
$paymentStatus = $_POST['payment_status'] ?? '';

$allowedStatuses = [
    'Pending',
    'Preparing',
    'Out for delivery',
    'Delivered',
    'Cancelled'
];

$allowedPaymentStatuses = [
    'Pending',
    'Paid',
    'Failed'
];

if (
    !$orderId ||
    !in_array($status, $allowedStatuses, true) ||
    !in_array($paymentStatus, $allowedPaymentStatuses, true)
) {
    $_SESSION['admin_error'] = "Invalid order information.";
    header("Location: manage_orders.php");
    exit;
}

$stmt = $pdo->prepare(
    "UPDATE orders
     SET status = ?, payment_status = ?
     WHERE order_id = ?"
);

$stmt->execute([$status, $paymentStatus, $orderId]);

$_SESSION['admin_success'] = $stmt->rowCount()
    ? "Order updated successfully."
    : "Order details were unchanged.";

header("Location: order_details.php?id=" . $orderId);
exit;
