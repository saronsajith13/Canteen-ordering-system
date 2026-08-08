<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-clipboard-list"></i> My Orders</h2>
    <?php if (count($orders) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></td>
                            <td>Rs<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo ucfirst($order['payment_method']); ?>
                                <?php if ($order['card_last_four']): ?>
                                    <br><small class="text-muted">****<?php echo htmlspecialchars($order['card_last_four']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if ($order['order_status'] === 'pending') $badge = 'warning';
                                elseif ($order['order_status'] === 'confirmed') $badge = 'info';
                                elseif ($order['order_status'] === 'preparing') $badge = 'primary';
                                elseif ($order['order_status'] === 'ready') $badge = 'success';
                                elseif ($order['order_status'] === 'completed') $badge = 'success';
                                elseif ($order['order_status'] === 'cancelled') $badge = 'danger';
                                ?>
                                <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($order['order_status']); ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['order_id']; ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <a href="print_invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="https://wa.me/?text=<?php echo urlencode("Canteen Order #{$order['order_id']} - Rs{$order['total_amount']} - {$order['order_status']}"); ?>" target="_blank" class="btn btn-sm btn-success">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php foreach ($orders as $order):
            $m_badge = 'secondary';
            if ($order['order_status'] === 'pending') $m_badge = 'warning';
            elseif ($order['order_status'] === 'confirmed') $m_badge = 'info';
            elseif ($order['order_status'] === 'preparing') $m_badge = 'primary';
            elseif ($order['order_status'] === 'ready') $m_badge = 'success';
            elseif ($order['order_status'] === 'completed') $m_badge = 'success';
            elseif ($order['order_status'] === 'cancelled') $m_badge = 'danger';
        ?>
            <div class="modal fade" id="orderModal<?php echo $order['order_id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Order #<?php echo $order['order_id']; ?> Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-<?php echo $m_badge; ?>"><?php echo ucfirst($order['order_status']); ?></span></p>
                            <p><strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?> (<?php echo ucfirst($order['payment_status']); ?>)
                                <?php if ($order['card_last_four']): ?>
                                    <br><small>Card: ****<?php echo htmlspecialchars($order['card_last_four']); ?></small>
                                <?php endif; ?>
                            </p>
                            <hr>
                            <h6>Items:</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $item_stmt = $pdo->prepare("SELECT oi.*, f.food_name FROM order_items oi LEFT JOIN food_items f ON oi.food_id = f.food_id WHERE oi.order_id = ?");
                                    $item_stmt->execute([$order['order_id']]);
                                    $items = $item_stmt->fetchAll();
                                    foreach ($items as $item):
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['food_name']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>Rs<?php echo number_format($item['subtotal'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>Rs<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <a href="print_invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary" target="_blank"><i class="fas fa-file-pdf"></i> Download PDF</a>
                            <a href="https://wa.me/?text=<?php echo urlencode("Canteen Order #{$order['order_id']} - Total: Rs{$order['total_amount']} - Status: {$order['order_status']}"); ?>" target="_blank" class="btn btn-success"><i class="fab fa-whatsapp"></i> Share</a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
            <p>No orders yet.</p>
            <a href="menu.php" class="btn btn-primary">Start Ordering</a>
        </div>
    <?php endif; ?>
</div>

<!-- Online Payment Details -->
<?php
$shop = $pdo->query("SELECT * FROM shop_settings WHERE id = 1")->fetch();
?>
<div class="text-center mt-3 mb-3">
    <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#onlineDetailsModal">
        <i class="fas fa-info-circle"></i> Online Payment Details
    </button>
</div>

<div class="modal fade" id="onlineDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Online Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Shop ID:</strong> <?php echo htmlspecialchars($shop['shop_id']); ?></p>
                <?php if ($shop['bank_name']): ?>
                    <p><strong>Bank:</strong> <?php echo htmlspecialchars($shop['bank_name']); ?></p>
                <?php endif; ?>
                <?php if ($shop['account_holder']): ?>
                    <p><strong>A/C Holder:</strong> <?php echo htmlspecialchars($shop['account_holder']); ?></p>
                <?php endif; ?>
                <?php if ($shop['account_number']): ?>
                    <p><strong>A/C No:</strong> <?php echo htmlspecialchars($shop['account_number']); ?></p>
                <?php endif; ?>
                <?php if ($shop['ifsc_code']): ?>
                    <p><strong>IFSC:</strong> <?php echo htmlspecialchars($shop['ifsc_code']); ?></p>
                <?php endif; ?>
                <?php if ($shop['upi_id']): ?>
                    <p><strong>UPI ID:</strong> <?php echo htmlspecialchars($shop['upi_id']); ?></p>
                <?php endif; ?>
                <?php if ($shop['qr_code_image']): ?>
                    <div class="text-center mt-2">
                        <img src="../assets/images/qr-codes/<?php echo htmlspecialchars($shop['qr_code_image']); ?>" alt="QR Code" style="max-height: 200px;">
                        <p class="text-muted small mt-1">Scan to pay</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
