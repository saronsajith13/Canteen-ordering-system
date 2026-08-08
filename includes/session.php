<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isCashier() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'cashier';
}

function isCashierOrAdmin() {
    return isAdmin() || isCashier();
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: ../customer/home.php');
        exit();
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

function redirectIfNotCashierOrAdmin() {
    if (!isCashierOrAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}
?>
