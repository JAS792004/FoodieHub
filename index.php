
<?php
require_once 'includes/header.php';

$categoryQuery = $pdo->query("SELECT * FROM categories ORDER BY category_id ASC");
$categories = $categoryQuery->fetchAll();

$foodQuery = $pdo->query("
    SELECT m.*, c.category_name
    FROM menu_items m
    INNER JOIN categories c ON m.category_id = c.category_id
    WHERE m.availability = 1
    ORDER BY m.item_id DESC
    LIMIT 8
");

$foods = $foodQuery->fetchAll();

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

$categoryIcons = [
    'Burgers' => 'bi-egg-fried',
    'Pizza' => 'bi-pie-chart',
    'Indian' => 'bi-cup-hot',
    'Chinese' => 'bi-bowl-hot',
    'Desserts' => 'bi-cake2',
    'Beverages' => 'bi-cup-straw'
];
?>

<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <div class="hero-label">
                    <i class="bi bi-stars"></i> Freshness Delivered Daily
                </div>

                <h1>Good Food.<br>Good Mood.<br><span>Great Moments.</span></h1>

                <p>
                    Discover delicious meals prepared with love and delivered
                    fresh to your doorstep. Your next favourite meal is just a click away.
                </p>

                <div class="hero-buttons">
                    <a href="customer/menu.php" class="btn btn-orange hero-btn">
                        Order Now <i class="bi bi-arrow-up-right"></i>
                    </a>
                    <a href="#popular" class="explore-link">
                        Explore Menu <i class="bi bi-arrow-down-right"></i>
                    </a>
                </div>

                <div class="hero-trust">
                    <div class="trust-icon"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <strong>Quick & Easy Ordering</strong>
                        <small>Delicious food, just a few clicks away</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 hero-image-area">
                <div class="hero-image-circle"></div>
                <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=1000" class="hero-image" alt="Delicious freshly prepared burger">
                <div class="floating-card floating-card-top">
                    <span class="floating-emoji">🍔</span>
                    <div>
                        <strong>Freshly Made</strong>
                        <small>Every single order</small>
                    </div>
                </div>
                <div class="floating-card floating-card-bottom">
                    <i class="bi bi-heart-fill"></i>
                    <div>
                        <strong>Made With Love</strong>
                        <small>For every food lover</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="benefits-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="benefit-item">
                    <i class="bi bi-truck"></i>
                    <div>
                        <strong>Fast Delivery</strong>
                        <small>Fresh food at your doorstep</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-item">
                    <i class="bi bi-award"></i>
                    <div>
                        <strong>Quality Food</strong>
                        <small>Carefully prepared meals</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-item">
                    <i class="bi bi-shield-check"></i>
                    <div>
                        <strong>Easy Ordering</strong>
                        <small>Simple and secure experience</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="categories-section section-padding" id="categories">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="section-eyebrow">EXPLORE OUR MENU</span>
                <h2>What are you <span>craving?</span></h2>
            </div>
            <a href="customer/menu.php" class="view-all">View All <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-3">
            <?php foreach ($categories as $category): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="customer/menu.php?category=<?= (int)$category['category_id'] ?>" class="category-card">
                        <div class="category-icon">
                            <i class="bi <?= $categoryIcons[$category['category_name']] ?? 'bi-restaurant' ?>"></i>
                        </div>
                        <h6><?= htmlspecialchars($category['category_name']) ?></h6>
                        <span>Explore <i class="bi bi-arrow-up-right"></i></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="popular-section section-padding" id="popular">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="section-eyebrow">CUSTOMER FAVOURITES</span>
                <h2>Popular <span>Dishes</span></h2>
                <p class="section-description">Discover some of our most loved dishes, freshly prepared for you.</p>
            </div>
            <a href="customer/menu.php" class="view-all">Full Menu <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            <?php foreach ($foods as $food): ?>
                <?php
                    $image = $foodImages[$food['image']] ?? 'https://images.unsplash.com/photo-1547592180-85f173990554?w=700';
                ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="food-card">
                        <div class="food-image">
                            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($food['name']) ?>" loading="lazy">
                            <span class="food-category"><?= htmlspecialchars($food['category_name']) ?></span>
                        </div>
                        <div class="food-details">
                            <h5><?= htmlspecialchars($food['name']) ?></h5>
                            <p><?= htmlspecialchars($food['description']) ?></p>
                            <div class="food-card-bottom">
                                <strong>₹<?= number_format((float)$food['price'], 2) ?></strong>
                                <a href="customer/menu.php" class="add-cart-btn" aria-label="Explore menu">
                                    <i class="bi bi-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($foods)): ?>
                <div class="col-12 text-center py-5">
                    <p>No food items are currently available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="about-section section-padding" id="about">
    <div class="container">
        <div class="about-banner">
            <div>
                <span class="section-eyebrow">A LITTLE ABOUT US</span>
                <h2>Good food brings<br>people <span>together.</span></h2>
                <p>At FoodieHub, we believe every meal deserves to be special. Explore our menu and find something you love.</p>
                <a href="customer/menu.php" class="btn btn-orange">Discover Our Menu <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="about-decoration"><i class="bi bi-egg-fried"></i></div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>
