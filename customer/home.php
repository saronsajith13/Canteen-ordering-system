<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$stmt = $pdo->query("SELECT f.*, c.category_name FROM food_items f LEFT JOIN categories c ON f.category_id = c.category_id WHERE f.status = 'available' ORDER BY f.food_id DESC LIMIT 8");
$items = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
        <form action="menu.php" method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search food..." required>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="row">
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="../assets/images/food-images/<?php echo htmlspecialchars($item['image'] ?: 'placeholder.svg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['food_name']); ?>" style="height: 180px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['food_name']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($item['category_name']); ?></p>
                            <p class="card-text"><strong>Rs<?php echo number_format($item['price'], 2); ?></strong></p>
                            <a href="menu.php?category=<?php echo $item['category_id']; ?>" class="btn btn-outline-primary btn-sm">View</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p>No items available. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
