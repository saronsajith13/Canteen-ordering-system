<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotAdmin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM food_items WHERE food_id = ?");
    $stmt->execute([$id]);
    header('Location: manage_food.php');
    exit();
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE food_items SET status = IF(status = 'available', 'unavailable', 'available') WHERE food_id = ?");
    $stmt->execute([$id]);
    header('Location: manage_food.php');
    exit();
}

$stmt = $pdo->query("SELECT f.*, c.category_name FROM food_items f LEFT JOIN categories c ON f.category_id = c.category_id ORDER BY f.food_id DESC");
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Foods</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-pizza-slice"></i> Manage Foods</h2>
                <a href="add_food.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New</a>
            </div>
            <hr>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['food_id']; ?></td>
                                        <td>
                                            <?php if ($item['image']): ?>
                                                <img src="../assets/images/food-images/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['food_name']); ?>" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['food_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></td>
                                        <td>Rs<?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $item['status'] === 'available' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="manage_food.php?toggle=<?php echo $item['food_id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-toggle-on"></i>
                                            </a>
                                            <a href="manage_food.php?delete=<?php echo $item['food_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
