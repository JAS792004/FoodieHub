
<?php
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: menu.php");
    exit;
}

$itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$itemId || !$quantity || $quantity < 1 || $quantity > 20) {
    header("Location: menu.php");
    exit;
}

try {
    $check = $pdo->prepare("
        SELECT item_id
        FROM menu_items
        WHERE item_id = ? AND availability = 1
    ");

    $check->execute([$itemId]);

    if (!$check->fetch()) {
        header("Location: menu.php");
        exit;
    }

    $existing = $pdo->prepare("
        SELECT cart_id, quantity
        FROM cart
        WHERE user_id = ? AND item_id = ?
    ");

    $existing->execute([$_SESSION['user_id'], $itemId]);
    $cartItem = $existing->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        $newQuantity = (int)$cartItem['quantity'] + $quantity;

        if ($newQuantity > 20) {
            $newQuantity = 20;
        }

        $update = $pdo->prepare("
            UPDATE cart
            SET quantity = ?
            WHERE cart_id = ? AND user_id = ?
        ");

        $update->execute([$newQuantity, $cartItem['cart_id'], $_SESSION['user_id']]);
    } else {
        $insert = $pdo->prepare("
            INSERT INTO cart (user_id, item_id, quantity)
            VALUES (?, ?, ?)
        ");

        $insert->execute([$_SESSION['user_id'], $itemId, $quantity]);
    }

    $_SESSION['cart_message'] = "Item added to your cart successfully.";

    header("Location: menu.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['cart_message'] = "Unable to add this item. Please try again.";
    header("Location: menu.php");
    exit;
}
