<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$order_id = (int)$_GET['id'];

if (isAdmin()) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
}
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT oi.*, f.food_name 
    FROM order_items oi 
    LEFT JOIN food_items f ON oi.food_id = f.food_id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$user_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->execute([$order['user_id']]);
$user = $user_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .invoice-box { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
        body { background: #f5f7fa; font-family: 'Courier New', monospace; }
        .invoice-box {
            max-width: 800px; margin: 40px auto; background: #fff;
            border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); padding: 40px;
        }
        .invoice-header { border-bottom: 2px solid #667eea; padding-bottom: 20px; margin-bottom: 20px; }
        .invoice-title { color: #667eea; font-size: 28px; font-weight: 700; }
        .invoice-table th { background: #667eea; color: #fff; }
        .total-row { font-size: 18px; font-weight: bold; background: #f0f4ff; }
        .status-badge { padding: 6px 16px; border-radius: 20px; font-size: 14px; }
        .btn-action { border-radius: 50px; padding: 10px 25px; margin: 5px; }
    </style>
</head>
<body>
    <div class="no-print text-center mt-3">
        <button onclick="window.print()" class="btn btn-primary btn-action"><i class="fas fa-download"></i> Download PDF</button>
        <a href="https://wa.me/?text=<?php echo urlencode("Canteen Order #{$order_id}\nTotal: Rs{$order['total_amount']}\nStatus: {$order['order_status']}\nDate: {$order['order_date']}"); ?>" target="_blank" class="btn btn-success btn-action"><i class="fab fa-whatsapp"></i> Share on WhatsApp</a>
        <a href="orders.php" class="btn btn-secondary btn-action"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="invoice-box">
        <div class="invoice-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="invoice-title"><i class="fas fa-utensils"></i> Canteen</h1>
                <p class="text-muted mb-0">Order Invoice</p>
            </div>
            <div class="text-end">
                <h3 class="mb-1">Invoice #<?php echo $order_id; ?></h3>
                <p class="text-muted mb-0"><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-6">
                <h6 class="fw-bold">Customer Details</h6>
                <p class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></p>
                <p class="mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="mb-0"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h6 class="fw-bold">Order Status</h6>
                <span class="status-badge bg-<?php
                    echo $order['order_status'] === 'pending' ? 'warning' :
                        ($order['order_status'] === 'confirmed' ? 'info' :
                        ($order['order_status'] === 'preparing' ? 'primary' :
                        ($order['order_status'] === 'ready' ? 'success' :
                        ($order['order_status'] === 'completed' ? 'success' : 'danger'))));
                ?> text-white"><?php echo ucfirst($order['order_status']); ?></span>
                <p class="mt-2 mb-0"><strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?> (<?php echo ucfirst($order['payment_status']); ?>)</p>
            </div>
        </div>

        <table class="table table-bordered invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($item['food_name']); ?></td>
                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                    <td class="text-end">Rs<?php echo number_format($item['quantity'] > 0 ? $item['subtotal'] / $item['quantity'] : 0, 2); ?></td>
                    <td class="text-end">Rs<?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-end fw-bold">Total Amount:</td>
                    <td class="text-end fw-bold">Rs<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center text-muted mt-4">
            <p class="mb-0">Thank you for your order!</p>
            <small>Canteen Ordering System</small>
        </div>
    </div>
</body>
</html>