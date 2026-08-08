<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotAdmin();

$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
$completed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'completed'")->fetchColumn();

$best_selling = $pdo->query("
    SELECT f.food_name, SUM(oi.quantity) as total_qty
    FROM order_items oi
    JOIN food_items f ON oi.food_id = f.food_id
    GROUP BY oi.food_id
    ORDER BY total_qty DESC
    LIMIT 5
")->fetchAll();

$recent_orders = $pdo->query("
    SELECT o.*, u.full_name FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="flex-grow-1 p-4">
            <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
            <hr>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5>Total Orders</h5>
                            <h3><?php echo $total_orders; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>Total Revenue</h5>
                            <h3>Rs<?php echo number_format($total_revenue, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5>Total Users</h5>
                            <h3><?php echo $total_users; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5>Pending Orders</h5>
                            <h3><?php echo $pending_orders; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Best Selling Items</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($best_selling as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['food_name']); ?></td>
                                            <td><?php echo $item['total_qty']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Recent Orders</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                            <td>Rs<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><?php echo ucfirst($order['order_status']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
