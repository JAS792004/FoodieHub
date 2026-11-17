
<?php
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit;
}

$cartId = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$cartId) {
    header("Location: cart.php");
    exit;
}

$check = $pdo->prepare("
    SELECT quantity
    FROM cart
    WHERE cart_id = ? AND user_id = ?
");

$check->execute([$cartId, $_SESSION['user_id']]);
$item = $check->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: cart.php");
    exit;
}

if ($action === 'remove') {
    $delete = $pdo->prepare("
        DELETE FROM cart
        WHERE cart_id = ? AND user_id = ?
    ");
    $delete->execute([$cartId, $_SESSION['user_id']]);

} elseif ($action === 'increase') {
    if ((int)$item['quantity'] < 20) {
        $update = $pdo->prepare("
            UPDATE cart SET quantity = quantity + 1
            WHERE cart_id = ? AND user_id = ?
        ");
        $update->execute([$cartId, $_SESSION['user_id']]);
    }

} elseif ($action === 'decrease') {
    if ((int)$item['quantity'] > 1) {
        $update = $pdo->prepare("
            UPDATE cart SET quantity = quantity - 1
            WHERE cart_id = ? AND user_id = ?
        ");
        $update->execute([$cartId, $_SESSION['user_id']]);
    }
}

header("Location: cart.php");
exit;
