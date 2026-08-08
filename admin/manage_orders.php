<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotCashierOrAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['order_status'];
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->execute([$status, $order_id]);
    header('Location: manage_orders.php');
    exit();
}

$status_filter = $_GET['status'] ?? '';
$sql = "SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.user_id";
if ($status_filter) {
    $sql .= " WHERE o.order_status = ?";
}
$sql .= " ORDER BY o.order_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($status_filter ? [$status_filter] : []);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="flex-grow-1 p-4">
            <h2><i class="fas fa-clipboard-list"></i> Manage Orders</h2>
            <hr>
            <div class="mb-3">
                <a href="manage_orders.php" class="btn btn-sm btn-secondary <?php echo !$status_filter ? 'active' : ''; ?>">All</a>
                <a href="manage_orders.php?status=pending" class="btn btn-sm btn-warning <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="manage_orders.php?status=confirmed" class="btn btn-sm btn-info <?php echo $status_filter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                <a href="manage_orders.php?status=preparing" class="btn btn-sm btn-primary <?php echo $status_filter === 'preparing' ? 'active' : ''; ?>">Preparing</a>
                <a href="manage_orders.php?status=ready" class="btn btn-sm btn-success <?php echo $status_filter === 'ready' ? 'active' : ''; ?>">Ready</a>
                <a href="manage_orders.php?status=completed" class="btn btn-sm btn-success <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">Completed</a>
                <a href="manage_orders.php?status=cancelled" class="btn btn-sm btn-danger <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td>Rs<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <?php echo ucfirst($order['payment_method']); ?> (<?php echo ucfirst($order['payment_status']); ?>)
                                            <?php if ($order['card_last_four']): ?>
                                                <br><small class="text-muted">Card: ****<?php echo htmlspecialchars($order['card_last_four']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                echo $order['order_status'] === 'pending' ? 'warning' :
                                                    ($order['order_status'] === 'confirmed' ? 'info' :
                                                    ($order['order_status'] === 'preparing' ? 'primary' :
                                                    ($order['order_status'] === 'ready' ? 'success' :
                                                    ($order['order_status'] === 'completed' ? 'success' : 'danger'))));
                                            ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-flex gap-1">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <select name="order_status" class="form-select form-select-sm" style="width: auto;">
                                                    <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="preparing" <?php echo $order['order_status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                    <option value="ready" <?php echo $order['order_status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                                    <option value="completed" <?php echo $order['order_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                            </form>
                                            <div class="mt-1 d-flex gap-1">
                                                <a href="../customer/print_invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                                                <a href="https://wa.me/?text=<?php echo urlencode("Canteen Order #{$order['order_id']} - Rs{$order['total_amount']} - {$order['order_status']}"); ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (count($orders) === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No orders found.</td>
                                    </tr>
                                <?php endif; ?>
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
