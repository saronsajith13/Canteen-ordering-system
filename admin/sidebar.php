<div class="d-flex flex-column admin-sidebar text-white">
    <div class="p-4 text-center border-bottom border-secondary">
        <h5 class="mb-1"><i class="fas fa-utensils"></i> <?php echo isAdmin() ? 'Canteen Admin' : 'Canteen Cashier'; ?></h5>
        <small class="text-white-50"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?></small>
    </div>
    <nav class="nav flex-column p-3">
        <?php if (isAdmin()): ?>
            <a class="nav-link text-white" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a class="nav-link text-white" href="manage_food.php"><i class="fas fa-pizza-slice"></i> Manage Foods</a>
            <a class="nav-link text-white" href="add_food.php"><i class="fas fa-plus-circle"></i> Add Food</a>
            <a class="nav-link text-white" href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
            <a class="nav-link text-white" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
            <a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> Payment Settings</a>
        <?php endif; ?>
        <a class="nav-link text-white" href="../admin/manage_orders.php"><i class="fas fa-clipboard-list"></i> Manage Orders</a>
        <hr class="border-secondary my-3">
        <a class="nav-link text-white-50" href="../index.php"><i class="fas fa-home"></i> Back to Site</a>
        <a class="nav-link text-warning" href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</div>
