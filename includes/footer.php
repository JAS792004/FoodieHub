
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <a class="navbar-brand footer-brand" href="<?= $basePath ?>index.php">
                    <span class="brand-icon"><i class="bi bi-egg-fried"></i></span>
                    Foodie<span>Hub.</span>
                </a>
                <p class="footer-description">
                    Bringing delicious food and happy moments together.
                    Your favourite meals are just a click away.
                </p>
                <div class="social-links">
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Explore</h6>
                <a href="<?= $basePath ?>index.php">Home</a>
                <a href="<?= $basePath ?>customer/menu.php">Our Menu</a>
                <a href="<?= $basePath ?>index.php#categories">Categories</a>
                <a href="<?= $basePath ?>index.php#about">About Us</a>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Account</h6>
                <a href="<?= $basePath ?>login.php">Login</a>
                <a href="<?= $basePath ?>register.php">Register</a>
                <a href="<?= $basePath ?>customer/cart.php">My Cart</a>
                <a href="<?= $basePath ?>customer/orders.php">My Orders</a>
            </div>

            <div class="col-lg-3">
                <h6>Get In Touch</h6>
                <p><i class="bi bi-envelope"></i> support@foodiehub.com</p>
                <p><i class="bi bi-geo-alt"></i> Noida, Uttar Pradesh, India</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?= date('Y') ?> FoodieHub. All rights reserved.</p>
            <p>Made with <i class="bi bi-heart-fill"></i> for food lovers.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $basePath ?>assets/js/script.js"></script>
</body>
</html>
