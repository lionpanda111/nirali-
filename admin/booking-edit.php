<?php
// Include the header
$page_title = 'Edit Booking';
require_once 'includes/header.php';

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid booking ID.';
    header('Location: bookings.php');
    exit();
}

$booking_id = (int)$_GET['id'];

// Fetch booking details
$query = "SELECT b.*, s.name as service_name, s.duration as service_duration 
          FROM bookings b 
          LEFT JOIN services s ON b.service_id = s.id 
          WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    $_SESSION['error'] = 'Booking not found.';
    header('Location: bookings.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $service_id = (int)$_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);
    $amount_paid = (float)$_POST['amount_paid'];
    
    // Basic validation
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (empty($service_id)) $errors[] = 'Please select a service.';
    if (empty($booking_date)) $errors[] = 'Booking date is required.';
    if (empty($booking_time)) $errors[] = 'Booking time is required.';
    if (!in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
        $errors[] = 'Invalid status selected.';
    }
    
    // If no validation errors, update the booking
    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE bookings 
            SET name = ?, email = ?, phone = ?, service_id = ?, 
                booking_date = ?, booking_time = ?, status = ?, notes = ?, 
                amount_paid = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            'sssissssdi',
            $name,
            $email,
            $phone,
            $service_id,
            $booking_date,
            $booking_time,
            $status,
            $notes,
            $amount_paid,
            $booking_id
        );
        
        if ($stmt->execute()) {
            // Log the status change if it was updated
            if ($status != $booking['status']) {
                $log_message = "Status changed from " . ucfirst($booking['status']) . " to " . ucfirst($status);
                $stmt = $conn->prepare("
                    INSERT INTO booking_history (booking_id, status, notes, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->bind_param('iss', $booking_id, $status, $log_message);
                $stmt->execute();
            }
            
            $_SESSION['success'] = 'Booking updated successfully.';
            header('Location: booking-view.php?id=' . $booking_id);
            exit();
        } else {
            $errors[] = 'Failed to update booking. Please try again.';
        }
    }
}

// Fetch all services for the dropdown
$services = $conn->query("SELECT id, name, duration FROM services WHERE status = 'active' ORDER BY name");
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Edit Booking #<?php echo $booking_id; ?></h4>
                    <a href="bookings.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Bookings
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($booking['name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($booking['email']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($booking['phone']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="service_id" class="form-label">Service <span class="text-danger">*</span></label>
                                    <select class="form-select" id="service_id" name="service_id" required>
                                        <option value="">Select a service</option>
                                        <?php 
                                        $services->data_seek(0); // Reset the result pointer
                                        while ($service = $services->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $service['id']; ?>" 
                                                    data-price="<?php echo $service['price']; ?>"
                                                    <?php echo ($service['id'] == $booking['service_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($service['name']); ?> 
                                                (₹<?php echo number_format($service['price'], 2); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="booking_date" class="form-label">Booking Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="booking_date" name="booking_date" 
                                           value="<?php echo htmlspecialchars($booking['booking_date']); ?>" required
                                           min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="booking_time" class="form-label">Booking Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="booking_time" name="booking_time" 
                                           value="<?php echo date('H:i', strtotime($booking['booking_time'])); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" <?php echo ($booking['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo ($booking['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo ($booking['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo ($booking['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount_paid" class="form-label">Amount Paid (₹)</label>
                                    <input type="number" step="0.01" class="form-control" id="amount_paid" name="amount_paid" 
                                           value="<?php echo number_format((float)($booking['amount_paid'] ?? 0), 2, '.', ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Amount (₹)</label>
                                    <input type="text" class="form-control" id="total_amount" 
                                           value="<?php echo number_format((float)($booking['total_amount'] ?? $booking['service_price']), 2); ?>" 
                                           readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($booking['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="booking-view.php?id=<?php echo $booking_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update total amount when service changes
document.getElementById('service_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || '0';
    document.getElementById('total_amount').value = parseFloat(price).toFixed(2);
});

// Initialize total amount on page load
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service_id');
    const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
    const price = selectedOption ? selectedOption.getAttribute('data-price') || '0' : '0';
    document.getElementById('total_amount').value = parseFloat(price).toFixed(2);
});
</script>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
