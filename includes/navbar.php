
<nav class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= $basePath ?>index.php">
            <span class="brand-icon"><i class="bi bi-egg-fried"></i></span>
            Foodie<span>Hub.</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?= $basePath ?>customer/menu.php">Our Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>index.php#categories">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>index.php#about">About Us</a></li>
            </ul>

            <div class="nav-actions">
                <a href="<?= $basePath ?>customer/cart.php" class="cart-link">
                    <i class="bi bi-bag"></i>
                    <span class="cart-count"><?= $cartCount ?></span>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= $basePath ?>customer/profile.php" class="btn btn-outline-dark login-btn">My Account</a>
                    <a href="<?= $basePath ?>logout.php" class="btn btn-orange">Logout</a>
                <?php else: ?>
                    <a href="<?= $basePath ?>login.php" class="btn btn-outline-dark login-btn">Login</a>
                    <a href="<?= $basePath ?>register.php" class="btn btn-orange">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
