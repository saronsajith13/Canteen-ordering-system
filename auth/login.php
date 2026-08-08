<?php
require_once '../config/db.php';
require_once '../includes/session.php';

redirectIfLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } elseif ($user['role'] === 'cashier') {
                header('Location: ../cashier/dashboard.php');
            } else {
                header('Location: ../customer/home.php');
            }
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'customer')");
            if ($stmt->execute([$full_name, $email, $hashed_password])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-btn { border: none; background: none; font-weight: 600; padding: 10px 30px; cursor: pointer; transition: all 0.3s ease; border-radius: 8px 8px 0 0; color: #6c757d; }
        .auth-btn.active { background: #667eea; color: #fff; }
        .form-box { display: none; }
        .form-box.active { display: block; animation: fadeInUp 0.4s ease; }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center" style="min-height: 100vh; align-items: center;">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <i class="fas fa-utensils fa-4x text-primary mb-3"></i>
                    <h2 class="fw-bold">Canteen System</h2>
                    <p class="text-muted">Order delicious food online</p>
                </div>

                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 pt-4">
                        <div class="d-flex justify-content-center gap-2">
                            <button class="auth-btn active" id="loginTab" onclick="showForm('login')"><i class="fas fa-sign-in-alt"></i> Login</button>
                            <button class="auth-btn" id="registerTab" onclick="showForm('register')"><i class="fas fa-user-plus"></i> Register</button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <div id="loginForm" class="form-box active">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary w-100 py-2">Login</button>
                            </form>
                        </div>

                        <div id="registerForm" class="form-box">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                <button type="submit" name="register" class="btn btn-success w-100 py-2">Register</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showForm(type) {
            document.querySelectorAll('.form-box').forEach(f => f.classList.remove('active'));
            document.querySelectorAll('.auth-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(type + 'Form').classList.add('active');
            document.getElementById(type + 'Tab').classList.add('active');
        }
        <?php if ($error && isset($_POST['register'])): ?>
        showForm('register');
        <?php elseif ($success): ?>
        showForm('login');
        <?php endif; ?>
    </script>
</body>
</html>