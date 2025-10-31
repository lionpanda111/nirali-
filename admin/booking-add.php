<?php
// Start session and include config
session_start();
require_once __DIR__ . '/../includes/config.php';

// Get database connection
try {
    $pdo = getDBConnection();
    if ($pdo === null) {
        throw new Exception("Failed to connect to database");
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Error: Could not connect to the database. Please try again later.");
}

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: login.php');
    exit();
}

// Initialize variables
$errors = [];
$success = '';
$booking = [
    'name' => '',
    'phone' => '',
    'service_id' => '',
    'booking_date' => date('Y-m-d'),
    'booking_time' => '',
    'status' => 'confirmed',
    'notes' => ''
];

// Fetch active services for dropdown
$services = [];
try {
    // First, check if the services table exists and has the required columns
    $stmt = $pdo->query("SHOW COLUMNS FROM services");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Build the query based on available columns
    $selectFields = ['id', 'title'];
    if (in_array('price', $columns)) {
        $selectFields[] = 'price';
    } else if (in_array('amount', $columns)) {
        $selectFields[] = 'amount as price';
    } else if (in_array('cost', $columns)) {
        $selectFields[] = 'cost as price';
    } else {
        $selectFields[] = '0 as price';
    }
    
    $query = "SELECT " . implode(', ', $selectFields) . " FROM services WHERE status = 1 ORDER BY title";
    $stmt = $pdo->query($query);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching services: ' . $e->getMessage());
    $errors[] = 'Error loading services. Please check the database configuration.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $booking['name'] = trim($_POST['name'] ?? '');
    $booking['phone'] = trim($_POST['phone'] ?? '');
    $booking['service_id'] = (int)($_POST['service_id'] ?? 0);
    $booking['booking_date'] = $_POST['booking_date'] ?? '';
    $booking['booking_time'] = $_POST['booking_time'] ?? '';
    $booking['status'] = $_POST['status'] ?? 'confirmed';
    $booking['notes'] = trim($_POST['notes'] ?? '');
    
    // Basic validation
    if (empty($booking['name'])) $errors[] = 'Customer name is required.';
    if (empty($booking['phone'])) $errors[] = 'Phone number is required.';
    if (empty($booking['service_id'])) $errors[] = 'Please select a service.';
    if (empty($booking['booking_time'])) $errors[] = 'Booking time is required.';
    
    // If no errors, proceed with database insertion
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("\n                INSERT INTO bookings (\n                    name, phone, service_id, booking_date, booking_time, \n                    status, notes, created_at, updated_at\n                ) VALUES (\n                    :name, :phone, :service_id, :booking_date, :booking_time, \n                    :status, :notes, NOW(), NOW()\n                )");
                
            $stmt->execute([
                ':name' => $booking['name'],
                ':phone' => $booking['phone'],
                ':service_id' => $booking['service_id'],
                ':booking_date' => $booking['booking_date'],
                ':booking_time' => $booking['booking_time'],
                ':status' => $booking['status'],
                ':notes' => $booking['notes']
            ]);
            
            $booking_id = $pdo->lastInsertId();
            
            $_SESSION['success'] = 'Booking added successfully!';
            header('Location: bookings.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Include the header
$page_title = 'Add New Booking';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Add New Booking</h4>
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
                                        <option value="">-- Select Service --</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?php echo $service['id']; ?>"
                                                <?php echo ($booking['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($service['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
                                           value="<?php echo htmlspecialchars($booking['booking_time']); ?>" required>
                                </div>
                            </div>
                        
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($booking['notes']); ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize any necessary JavaScript here
</script>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
