<?php
// Start the session
session_start();

// Include the database configuration
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'You must be logged in as an admin to access this page.';
    header('Location: ../login.php');
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid booking ID.';
    header('Location: bookings.php');
    exit();
}

$booking_id = (int)$_GET['id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Check if booking exists
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ?");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Booking not found.');
    }
    
    // Delete related records first (if any)
    // 1. Delete from booking_history
    $stmt = $conn->prepare("DELETE FROM booking_history WHERE booking_id = ?");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    
    // 2. Delete from booking_services (if exists)
    $stmt = $conn->prepare("DELETE FROM booking_services WHERE booking_id = ?");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    
    // 3. Delete from payments (if exists)
    $stmt = $conn->prepare("DELETE FROM payments WHERE booking_id = ?");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    
    // Finally, delete the booking
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    
    // Commit the transaction
    $conn->commit();
    
    $_SESSION['success'] = 'Booking deleted successfully.';
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    $_SESSION['error'] = 'Failed to delete booking: ' . $e->getMessage();
}

// Redirect back to bookings page
header('Location: bookings.php');
exit();
