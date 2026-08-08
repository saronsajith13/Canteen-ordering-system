<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotAdmin();

$daily_sales = $pdo->query("
    SELECT DATE(order_date) as date, COUNT(*) as orders, SUM(total_amount) as revenue
    FROM orders
    WHERE order_status = 'completed'
    GROUP BY DATE(order_date)
    ORDER BY date DESC LIMIT 30
")->fetchAll();

$category_sales = $pdo->query("
    SELECT c.category_name, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_revenue
    FROM order_items oi
    JOIN food_items f ON oi.food_id = f.food_id
    JOIN categories c ON f.category_id = c.category_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.order_status = 'completed'
    GROUP BY c.category_id
    ORDER BY total_revenue DESC
")->fetchAll();

$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status = 'completed'")->fetchColumn();
$total_orders_completed = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'completed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="flex-grow-1 p-4">
            <h2><i class="fas fa-chart-bar"></i> Sales Reports</h2>
            <hr>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>Total Revenue (Completed)</h5>
                            <h3>Rs<?php echo number_format($total_revenue, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5>Total Orders Completed</h5>
                            <h3><?php echo $total_orders_completed; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-7 mb-4">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Daily Sales (Last 30 Days)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Orders</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($daily_sales as $sale): ?>
                                            <tr>
                                                <td><?php echo $sale['date']; ?></td>
                                                <td><?php echo $sale['orders']; ?></td>
                                                <td>Rs<?php echo number_format($sale['revenue'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (count($daily_sales) === 0): ?>
                                            <tr><td colspan="3" class="text-center">No data available.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 mb-4">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Category-wise Sales</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Qty Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($category_sales as $cat): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                            <td><?php echo $cat['total_qty']; ?></td>
                                            <td>Rs<?php echo number_format($cat['total_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($category_sales) === 0): ?>
                                        <tr><td colspan="3" class="text-center">No data available.</td></tr>
                                    <?php endif; ?>
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
