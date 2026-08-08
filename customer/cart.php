<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotLoggedIn();

if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $food_id => $qty) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$food_id]);
        } else {
            $_SESSION['cart'][$food_id] = (int)$qty;
        }
    }
    header('Location: cart.php');
    exit();
}

if (isset($_GET['remove'])) {
    $food_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$food_id]);
    header('Location: cart.php');
    exit();
}

$cart_items = [];
$total_amount = 0;

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM food_items WHERE food_id IN ($placeholders) AND status = 'available'");
    $stmt->execute($ids);
    $foods = $stmt->fetchAll();

    foreach ($foods as $food) {
        $qty = $_SESSION['cart'][$food['food_id']];
        $subtotal = $food['price'] * $qty;
        $total_amount += $subtotal;
        $cart_items[] = [
            'food' => $food,
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>
    <?php if (count($cart_items) > 0): ?>
        <form method="POST">
            <div class="table-responsive">
                <table class="table table-bordered mt-3">
                    <thead class="table-dark">
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['food']['food_name']); ?></td>
                                <td>Rs<?php echo number_format($item['food']['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantity[<?php echo $item['food']['food_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="0" max="20" class="form-control" style="width: 80px;">
                                </td>
                                <td>Rs<?php echo number_format($item['subtotal'], 2); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $item['food']['food_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td colspan="2"><strong>Rs<?php echo number_format($total_amount, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" name="update_cart" class="btn btn-warning">Update Cart</button>
                <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            </div>
        </form>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <p>Your cart is empty.</p>
            <a href="menu.php" class="btn btn-primary">Browse Menu</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
