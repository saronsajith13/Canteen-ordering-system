<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header('Location: menu.php');
    exit();
}

$shop = $pdo->query("SELECT * FROM shop_settings WHERE id = 1")->fetch();

$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM food_items WHERE food_id IN ($placeholders) AND status = 'available'");
$stmt->execute($ids);
$foods = $stmt->fetchAll();

$total_amount = 0;
foreach ($foods as $food) {
    $total_amount += $food['price'] * $_SESSION['cart'][$food['food_id']];
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $user_id = $_SESSION['user_id'];
    $card_holder_name = trim($_POST['card_holder_name'] ?? '');
    $card_number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
    $card_last_four = '';

    if ($payment_method === 'online') {
        if (empty($card_holder_name)) {
            $error = 'Cardholder name is required.';
        } elseif (strlen($card_number) < 13) {
            $error = 'Please enter a valid card number.';
        } elseif (empty($_POST['card_expiry'])) {
            $error = 'Card expiry date is required.';
        } elseif (empty($_POST['card_cvv'])) {
            $error = 'Card CVV is required.';
        } else {
            $card_last_four = substr($card_number, -4);
        }
    }

    if (empty($error)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, order_status, payment_method, payment_status, card_holder_name, card_last_four) VALUES (?, ?, 'pending', ?, 'unpaid', ?, ?)");
            $stmt->execute([$user_id, $total_amount, $payment_method, $card_holder_name, $card_last_four]);
            $order_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, food_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
            foreach ($foods as $food) {
                $qty = $_SESSION['cart'][$food['food_id']];
                $subtotal = $food['price'] * $qty;
                $stmt->execute([$order_id, $food['food_id'], $qty, $subtotal]);
            }

            if ($payment_method === 'online') {
                $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE order_id = ?");
                $stmt->execute([$order_id]);
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            $success = "Order placed successfully! Order ID: #$order_id";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to place order. Please try again.';
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <?php if ($success): ?>
        <div class="alert alert-success text-center">
            <h4><i class="fas fa-check-circle"></i> <?php echo $success; ?></h4>
            <p>Thank you for your order!</p>
            <a href="orders.php" class="btn btn-primary">View Orders</a>
            <a href="menu.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Order Summary</h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($foods as $food): ?>
                                    <?php $qty = $_SESSION['cart'][$food['food_id']]; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($food['food_name']); ?></td>
                                        <td><?php echo $qty; ?></td>
                                        <td>Rs<?php echo number_format($food['price'], 2); ?></td>
                                        <td>Rs<?php echo number_format($food['price'] * $qty, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>Rs<?php echo number_format($total_amount, 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Payment</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="cash" id="cash" checked>
                                    <label class="form-check-label" for="cash">Cash on Delivery</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="online" id="online">
                                    <label class="form-check-label" for="online">Online Payment</label>
                                </div>
                            </div>

                            <div id="onlineSection" class="d-none">
                                <div class="alert alert-info p-3">
                                    <h6 class="mb-2"><i class="fas fa-university"></i> Bank Payment Details</h6>
                                    <p class="mb-1"><strong>Shop ID:</strong> <?php echo htmlspecialchars($shop['shop_id']); ?></p>
                                    <?php if ($shop['bank_name']): ?>
                                        <p class="mb-1"><strong>Bank:</strong> <?php echo htmlspecialchars($shop['bank_name']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($shop['account_holder']): ?>
                                        <p class="mb-1"><strong>A/C Holder:</strong> <?php echo htmlspecialchars($shop['account_holder']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($shop['account_number']): ?>
                                        <p class="mb-1"><strong>A/C No:</strong> <?php echo htmlspecialchars($shop['account_number']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($shop['ifsc_code']): ?>
                                        <p class="mb-1"><strong>IFSC:</strong> <?php echo htmlspecialchars($shop['ifsc_code']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($shop['upi_id']): ?>
                                        <p class="mb-1"><strong>UPI ID:</strong> <?php echo htmlspecialchars($shop['upi_id']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($shop['qr_code_image']): ?>
                                        <div class="mt-2 text-center">
                                            <img src="../assets/images/qr-codes/<?php echo htmlspecialchars($shop['qr_code_image']); ?>" alt="QR Code" style="max-height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <hr>
                                <h6><i class="fas fa-credit-card"></i> Card Details</h6>
                                <div class="mb-3">
                                    <label class="form-label">Cardholder Name</label>
                                    <input type="text" name="card_holder_name" class="form-control" placeholder="Name on card">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Expiry</label>
                                        <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" name="card_cvv" class="form-control" placeholder="123" maxlength="4">
                                    </div>
                                </div>
                            </div>

                            <p class="text-muted small">Total: <strong>Rs<?php echo number_format($total_amount, 2); ?></strong></p>
                            <button type="submit" class="btn btn-success w-100">Place Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(el => {
    el.addEventListener('change', function() {
        const section = document.getElementById('onlineSection');
        if (this.value === 'online') {
            section.classList.remove('d-none');
        } else {
            section.classList.add('d-none');
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>
