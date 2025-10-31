<?php
/**
 * Admin Authentication Helper
 * 
 * This file contains functions to handle admin authentication and authorization.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the main config file
require_once __DIR__ . '/../../includes/config.php';

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

/**
 * Require admin login
 * Redirects to login page if user is not logged in
 */
function requireAdminLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

/**
 * Require admin role
 * Redirects to dashboard if user is not an admin
 */
function requireAdminRole() {
    requireAdminLogin();
    
    if (!isAdmin()) {
        $_SESSION['error_message'] = 'You do not have permission to access this page.';
        header('Location: index.php');
        exit();
    }
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if token is valid, false otherwise
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if current page is active
 * 
 * @param string $page Page name to check
 * @return string 'active' if current page matches, empty string otherwise
 */
function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page === $page) ? 'active' : '';
}

// Check if user is trying to access admin area without logging in
$admin_pages = ['index.php', 'profile.php', 'blog.php', 'blog-edit.php', 'blog-categories.php', 
               'services.php', 'gallery.php', 'testimonials.php', 'bookings.php', 'messages.php'];

$current_page = basename($_SERVER['PHP_SELF']);

// If trying to access admin page but not logged in, redirect to login
if (in_array($current_page, $admin_pages) && !isLoggedIn() && $current_page !== 'login.php') {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

// If logged in and trying to access login page, redirect to dashboard
if (isLoggedIn() && $current_page === 'login.php') {
    header('Location: index.php');
    exit();
}
?>