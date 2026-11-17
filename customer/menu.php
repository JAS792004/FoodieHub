
<?php
require_once '../includes/header.php';

$search = trim($_GET['search'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);

$categoryQuery = $pdo->query("SELECT * FROM categories ORDER BY category_name");
$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT m.*, c.category_name
    FROM menu_items m
    INNER JOIN categories c ON m.category_id = c.category_id
    WHERE m.availability = 1
";

$params = [];

if ($categoryId && $categoryId > 0) {
    $sql .= " AND m.category_id = ?";
    $params[] = $categoryId;
}

if ($search !== '') {
    $sql .= " AND (m.name LIKE ? OR m.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY m.item_id DESC";

$foodQuery = $pdo->prepare($sql);
$foodQuery->execute($params);
$foods = $foodQuery->fetchAll(PDO::FETCH_ASSOC);

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
?>

<section class="menu-hero">
    <div class="container text-center">
        <span class="section-eyebrow">FRESHLY PREPARED FOR YOU</span>
        <h1>Explore Our <span>Menu</span></h1>
        <p>Discover your favourites and find something delicious today.</p>

        <form method="GET" class="menu-search">
            <?php if ($categoryId): ?>
                <input type="hidden" name="category" value="<?= (int)$categoryId ?>">
            <?php endif; ?>
            <i class="bi bi-search"></i>
            <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search burgers, pizza, desserts...">
            <button type="submit" class="btn btn-orange">Search</button>
        </form>
    </div>
</section>

<section class="menu-section section-padding">
    <div class="container">
        <div class="menu-category-list">
            <?php if (isset($_SESSION['cart_message'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['cart_message']) ?>
    </div>
    <?php unset($_SESSION['cart_message']); ?>
<?php endif; ?>
            <a href="menu.php" class="menu-filter <?= !$categoryId ? 'selected' : '' ?>">All Items</a>

            <?php foreach ($categories as $category): ?>
                <a href="menu.php?category=<?= (int)$category['category_id'] ?>"
                   class="menu-filter <?= $categoryId == $category['category_id'] ? 'selected' : '' ?>">
                    <?= htmlspecialchars($category['category_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="menu-result-heading">
            <div>
                <h3>Our Delicious Selection</h3>
                <p><?= count($foods) ?> dishes available</p>
            </div>
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

                                <form method="POST" action="add_to_cart.php">
                                    <input type="hidden" name="item_id" value="<?= (int)$food['item_id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="add-cart-btn" aria-label="Add <?= htmlspecialchars($food['name']) ?> to cart">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($foods)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-4 text-secondary"></i>
                    <h4 class="mt-3">No dishes found</h4>
                    <p class="text-secondary">Try another search or category.</p>
                    <a href="menu.php" class="btn btn-orange">View All Dishes</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
