<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo isLoggedIn() ? '../customer/home.php' : '../auth/login.php'; ?>">
            <i class="fas fa-utensils"></i> Canteen
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isLoggedIn() ? '../customer/home.php' : '../auth/login.php'; ?>">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../customer/menu.php">
                        <i class="fas fa-book-open"></i> Menu
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../customer/cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../customer/orders.php">
                            <i class="fas fa-clipboard-list"></i> My Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../customer/profile.php">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Admin
                            </a>
                        </li>
                    <?php elseif (isCashier()): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-cashier" href="../cashier/dashboard.php">
                                <i class="fas fa-cash-register"></i> Cashier
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item ms-1">
                        <a class="nav-link nav-logout" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>