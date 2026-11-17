
<?php
require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

$name = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $phone === '' || $address === '' || $password === '' || $confirmPassword === '') {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = "Please enter a valid phone number.";
    } elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = "An account with this email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare("
                INSERT INTO users (name, email, password, phone, address, role)
                VALUES (?, ?, ?, ?, ?, 'customer')
            ");

            if ($insert->execute([$name, $email, $hashedPassword, $phone, $address])) {
                $success = "Registration successful! You can now log in.";
                $name = '';
                $email = '';
                $phone = '';
                $address = '';
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | FoodieHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-brand">
            <a href="index.php" class="navbar-brand">
                <span class="brand-icon"><i class="bi bi-egg-fried"></i></span>
                Foodie<span>Hub.</span>
            </a>
        </div>

        <div class="auth-card">
            <div class="auth-heading">
                <div class="auth-icon"><i class="bi bi-person-plus"></i></div>
                <h2>Create Your Account</h2>
                <p>Join FoodieHub and discover delicious food.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your full name" value="<?= htmlspecialchars($name) ?>" maxlength="100" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" value="<?= htmlspecialchars($email) ?>" maxlength="150" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" placeholder="Enter your phone number" value="<?= htmlspecialchars($phone) ?>" maxlength="15" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Delivery Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Enter your delivery address" required><?= htmlspecialchars($address) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" minlength="8" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter your password" required>
                </div>

                <button type="submit" class="btn btn-orange w-100 auth-submit">
                    Create Account <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="auth-switch">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>

        <p class="auth-back"><a href="index.php"><i class="bi bi-arrow-left"></i> Back to Homepage</a></p>
    </div>
</div>

</body>
</html>
