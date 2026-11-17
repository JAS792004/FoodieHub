
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/database.php";

if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password']) && $user['role'] === 'admin') {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid admin credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | FoodieHub</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body class="login-page">

<div class="login-container">
    <div class="login-logo">Foodie<span>Hub.</span></div>
    <h2>Admin Login</h2>
    <p class="login-subtitle">Sign in to manage your restaurant</p>

    <?php if ($error !== ""): ?>
        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="Enter admin email" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" required>

        <button type="submit" class="admin-button">Login to Dashboard</button>
    </form>

    <a href="../index.php" class="back-link">Back to FoodieHub</a>
</div>

</body>
</html>
