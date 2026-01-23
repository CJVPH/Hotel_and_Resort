<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Get current admin ID
function getAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

// Get current admin username
function getAdminUsername() {
    return $_SESSION['admin_username'] ?? null;
}

// Get current admin full name
function getAdminFullName() {
    return $_SESSION['admin_full_name'] ?? null;
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Logout admin
function adminLogout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

