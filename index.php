<?php
require_once 'includes/session.php';

if (isLoggedIn()) {
    header('Location: customer/home.php');
} else {
    header('Location: auth/login.php');
}
exit();
