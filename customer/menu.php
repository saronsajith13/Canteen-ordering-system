<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT f.*, c.category_name FROM food_items f LEFT JOIN categories c ON f.category_id = c.category_id WHERE f.status = 'available'";
$params = [];

if (!empty($search)) {
    $sql .= " AND (f.food_name LIKE ? OR f.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $sql .= " AND f.category_id = ?";
    $params[] = $category;
}

$sql .= " ORDER BY f.food_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
$categories = $cat_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $food_id = (int)$_POST['food_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$food_id])) {
        $_SESSION['cart'][$food_id]++;
    } else {
        $_SESSION['cart'][$food_id] = 1;
    }
    header('Location: menu.php');
    exit();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="menu.php" class="list-group-item list-group-item-action <?php echo empty($category) ? 'active' : ''; ?>">All</a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="menu.php?category=<?php echo $cat['category_id']; ?>" class="list-group-item list-group-item-action <?php echo ($category == $cat['category_id']) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Our Menu</h3>
                <form action="menu.php" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="row">
                <?php if (count($items) > 0): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <img src="../assets/images/food-images/<?php echo htmlspecialchars($item['image'] ?: 'placeholder.svg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['food_name']); ?>" style="height: 180px; object-fit: cover;">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['food_name']); ?></h5>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                    <p class="card-text small"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <p class="card-text mt-auto"><strong>Rs<?php echo number_format($item['price'], 2); ?></strong></p>
                                    <form method="POST">
                                        <input type="hidden" name="food_id" value="<?php echo $item['food_id']; ?>">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary w-100">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No items found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
