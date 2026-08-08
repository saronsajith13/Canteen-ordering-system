<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotCashierOrAdmin();

$today_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(order_date) = CURDATE()")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../admin/sidebar.php'; ?>
        <div class="flex-grow-1 p-4">
            <h2><i class="fas fa-cash-register"></i> Cashier Dashboard</h2>
            <hr>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5>Today's Orders</h5>
                            <h3><?php echo $today_orders; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5>Pending Orders</h5>
                            <h3><?php echo $pending_orders; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5>Total Orders</h5>
                            <h3><?php echo $total_orders; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="../admin/manage_orders.php" class="btn btn-lg btn-primary">
                    <i class="fas fa-clipboard-list"></i> Manage Orders
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
