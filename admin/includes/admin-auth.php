<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = 'Please log in to access the admin panel';
    header('Location: login.php');
    exit;
}

// Verify admin role if needed
if (isset($required_role) && !empty($required_role)) {
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== $required_role) {
        $_SESSION['error'] = 'You do not have permission to access this page';
        header('Location: index.php');
        exit;
    }
}

// Include database connection
require_once __DIR__ . '/../../includes/db.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Function to sanitize input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
