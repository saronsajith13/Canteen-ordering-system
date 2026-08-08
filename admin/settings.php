<?php
require_once '../config/db.php';
require_once '../includes/session.php';
redirectIfNotAdmin();

$stmt = $pdo->query("SELECT * FROM shop_settings WHERE id = 1");
$settings = $stmt->fetch();

if (!$settings) {
    $pdo->exec("INSERT INTO shop_settings (id, shop_name, shop_id) VALUES (1, 'Canteen', 'CANTEEN001')");
    $stmt = $pdo->query("SELECT * FROM shop_settings WHERE id = 1");
    $settings = $stmt->fetch();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_name = trim($_POST['shop_name']);
    $shop_id = trim($_POST['shop_id']);
    $bank_name = trim($_POST['bank_name']);
    $account_holder = trim($_POST['account_holder']);
    $account_number = trim($_POST['account_number']);
    $ifsc_code = trim($_POST['ifsc_code']);
    $upi_id = trim($_POST['upi_id']);

    if (empty($shop_name) || empty($shop_id)) {
        $error = 'Shop Name and Shop ID are required.';
    } else {
        $qr = $settings['qr_code_image'];
        if (!empty($_FILES['qr_code']['name'])) {
            $target_dir = '../assets/images/qr-codes/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $qr = time() . '_' . basename($_FILES['qr_code']['name']);
            move_uploaded_file($_FILES['qr_code']['tmp_name'], $target_dir . $qr);
            if (!empty($settings['qr_code_image']) && file_exists($target_dir . $settings['qr_code_image'])) {
                unlink($target_dir . $settings['qr_code_image']);
            }
        }

        $stmt = $pdo->prepare("UPDATE shop_settings SET shop_name = ?, shop_id = ?, bank_name = ?, account_holder = ?, account_number = ?, ifsc_code = ?, upi_id = ?, qr_code_image = ? WHERE id = 1");
        $stmt->execute([$shop_name, $shop_id, $bank_name, $account_holder, $account_number, $ifsc_code, $upi_id, $qr]);

        $stmt = $pdo->query("SELECT * FROM shop_settings WHERE id = 1");
        $settings = $stmt->fetch();
        $success = 'Settings updated successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Payment Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="flex-grow-1 p-4">
            <h2><i class="fas fa-cog"></i> Online Payment Settings</h2>
            <hr>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Shop & Bank Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shop_name" class="form-label">Shop Name</label>
                                <input type="text" class="form-control" id="shop_name" name="shop_name" value="<?php echo htmlspecialchars($settings['shop_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shop_id" class="form-label">Shop ID</label>
                                <input type="text" class="form-control" id="shop_id" name="shop_id" value="<?php echo htmlspecialchars($settings['shop_id']); ?>" required>
                            </div>
                        </div>
                        <hr>
                        <h5>Bank Account Details</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($settings['bank_name']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="account_holder" class="form-label">Account Holder Name</label>
                                <input type="text" class="form-control" id="account_holder" name="account_holder" value="<?php echo htmlspecialchars($settings['account_holder']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="account_number" name="account_number" value="<?php echo htmlspecialchars($settings['account_number']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" value="<?php echo htmlspecialchars($settings['ifsc_code']); ?>">
                            </div>
                        </div>
                        <hr>
                        <h5>UPI Payment</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="upi_id" class="form-label">UPI ID</label>
                                <input type="text" class="form-control" id="upi_id" name="upi_id" value="<?php echo htmlspecialchars($settings['upi_id']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="qr_code" class="form-label">QR Code Image</label>
                                <input type="file" class="form-control" id="qr_code" name="qr_code" accept="image/*">
                                <?php if ($settings['qr_code_image']): ?>
                                    <div class="mt-2">
                                        <img src="../assets/images/qr-codes/<?php echo htmlspecialchars($settings['qr_code_image']); ?>" alt="QR Code" style="max-height: 150px;">
                                        <p class="text-muted small mt-1">Current QR code. Upload a new one to replace.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
