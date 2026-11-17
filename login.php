
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
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please enter your email and password.";
    } else {
        $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $query->execute([$email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Invalid email address or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FoodieHub</title>
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
                <div class="auth-icon"><i class="bi bi-person-circle"></i></div>
                <h2>Welcome Back!</h2>
                <p>Login to continue your delicious journey.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-orange w-100 auth-submit">
                    Login <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="auth-switch">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>

        <p class="auth-back"><a href="index.php"><i class="bi bi-arrow-left"></i> Back to Homepage</a></p>
    </div>
</div>

</body>
</html>
