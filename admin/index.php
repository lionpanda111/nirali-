<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database configuration
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
    $_SESSION['error'] = 'You must be logged in to access the admin panel.';
    header('Location: login.php');
    exit();
}

// Check if user has admin role
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = 'You do not have permission to access this page.';
    header('Location: login.php');
    exit();
}

// Get counts for dashboard
$counts = [
    'bookings' => 0,
    'pending_bookings' => 0,
    'testimonials' => 0,
    'unread_messages' => 0,
    'services' => 0,
    'service_categories' => 0
];

try {
    // Get total bookings count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['bookings'] = $result ? (int)$result['count'] : 0;
    
    // Get pending bookings count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['pending_bookings'] = $result ? (int)$result['count'] : 0;
    
    // Get gallery items count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_images WHERE status = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['gallery_items'] = $result ? (int)$result['count'] : 0;
    
    // Get testimonials count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM testimonials WHERE status = 'approved'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['testimonials'] = $result ? (int)$result['count'] : 0;
    
    // Get unread messages count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['unread_messages'] = $result ? (int)$result['count'] : 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE status = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['services'] = $result ? (int)$result['count'] : 0;
    
        // Get service categories count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM service_categories");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['service_categories'] = $result ? (int)$result['count'] : 0;
    
    // Instagram Reels functionality has been removed
    // Get recent bookings
    $recent_bookings = [];
    $stmt = $pdo->query(
        "SELECT b.*, 
                b.customer_name as name,
                b.customer_email as email,
                b.customer_phone as phone
         FROM bookings b 
         ORDER BY b.booking_date DESC, b.booking_time DESC
         LIMIT 5"
    );
    
    if ($stmt) {
        $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get recent testimonials
    $recent_testimonials = [];
    $stmt = $pdo->query(
        "SELECT * FROM testimonials 
         WHERE status = 'approved'
         ORDER BY created_at DESC 
         LIMIT 5"
    );
    
    if ($stmt) {
        $recent_testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error loading dashboard data: ' . $e->getMessage();
}

// Set page title
$page_title = 'Dashboard';

// Include the header
require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Overview</li>
    </ol>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Stats Cards -->
    <div class="row g-4">
        <!-- Total Bookings -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card border-start-primary border-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase text-primary fw-bold small mb-1">Total Bookings</div>
                            <div class="h4 mb-0 fw-bold"><?php echo number_format($counts['bookings']); ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-2">
                    <a href="bookings.php" class="small text-primary text-decoration-none">View All</a>
                    <i class="fas fa-chevron-right small text-primary"></i>
                </div>
            </div>
        </div>

        <!-- Pending Bookings -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card border-start-warning border-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase text-warning fw-bold small mb-1">Pending Bookings</div>
                            <div class="h4 mb-0 fw-bold"><?php echo number_format($counts['pending_bookings']); ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-2">
                    <a href="bookings.php?status=pending" class="small text-warning text-decoration-none">View Pending</a>
                    <i class="fas fa-chevron-right small text-warning"></i>
                </div>
            </div>
        </div>

        <!-- Gallery Items -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card border-start-success border-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase text-success fw-bold small mb-1">Gallery Items</div>
                            <div class="h4 mb-0 fw-bold"><?php echo number_format($counts['gallery_items']); ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-images fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-2">
                    <a href="gallery.php" class="small text-success text-decoration-none">Manage Gallery</a>
                    <i class="fas fa-chevron-right small text-success"></i>
                </div>
            </div>
        </div>
        
      
        
        <!-- Services Management -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card border-start-info border-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase text-info fw-bold small mb-1">Services</div>
                            <div class="h4 mb-0 fw-bold"><?php echo number_format($counts['services']); ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-spa fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-2">
                    <a href="services.php" class="small text-info text-decoration-none">Manage Services</a>
                    <i class="fas fa-chevron-right small text-info"></i>
                </div>
            </div>
        </div>
        
        <!-- Service Categories -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card border-start-secondary border-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase text-secondary fw-bold small mb-1">Categories</div>
                            <div class="h4 mb-0 fw-bold"><?php echo number_format($counts['service_categories']); ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-2">
                    <a href="service-categories.php" class="small text-secondary text-decoration-none">View Categories</a>
                    <i class="fas fa-chevron-right small text-secondary"></i>
                </div>
            </div>
        </div>
        
        
        
        <!-- Testimonials -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card border-start-purple border-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase text-purple fw-bold small mb-1">Testimonials</div>
                            <div class="h4 mb-0 fw-bold"><?php echo number_format($counts['testimonials']); ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-comments fa-2x text-purple-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-2">
                    <a href="testimonials.php" class="small text-purple text-decoration-none">View Testimonials</a>
                    <i class="fas fa-chevron-right small text-purple"></i>
                </div>
            </div>
        </div>
        
        <!-- Unread Messages -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card border-start-danger border-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase text-danger fw-bold small mb-1">Unread Messages</div>
                            <div class="h4 mb-0 fw-bold"><?php echo number_format($counts['unread_messages']); ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-envelope fa-2x text-danger-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-2">
                    <a href="messages.php" class="small text-danger text-decoration-none">View Messages</a>
                    <i class="fas fa-chevron-right small text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity Row -->
    <div class="row mt-4">
        <!-- Recent Bookings -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check me-1"></i>
                        Recent Bookings
                    </h5>
                    <a href="bookings.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_bookings)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No recent bookings found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_bookings as $index => $booking): 
                                        $customer_name = $booking['customer_name'] ?? 'Guest';
                                        $customer_email = $booking['customer_email'] ?? '';
                                    ?>
                                        <tr>
                                            <td class="align-middle"><?php echo $index + 1; ?></td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-sm bg-light rounded-circle text-primary d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($customer_name); ?></h6>
                                                        <?php if (!empty($customer_email)): ?>
                                                            <small class="text-muted"><?php echo htmlspecialchars($customer_email); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <?php 
                                                $date = new DateTime($booking['booking_date']);
                                                echo '<div class="fw-medium">' . $date->format('M j, Y') . '</div>';
                                                echo '<small class="text-muted">' . date('g:i A', strtotime($booking['booking_time'])) . '</small>';
                                                ?>
                                            </td>
                                            <td class="align-middle">
                                                <?php
                                                $status_class = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'success',
                                                    'completed' => 'info',
                                                    'cancelled' => 'danger'
                                                ][$booking['status'] ?? 'pending'] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-soft-<?php echo $status_class; ?> text-<?php echo $status_class; ?> p-2">
                                                    <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="booking-view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="booking-edit.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Testimonials -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-quote-left me-1"></i>
                        Recent Testimonials
                    </h5>
                    <a href="testimonials.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_testimonials)): ?>
                        <div class="text-center p-4">
                            <i class="fas fa-comment-slash text-muted mb-3" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">No recent testimonials</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_testimonials as $testimonial): ?>
                                <div class="list-group-item border-0 py-3 px-4">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="avatar-sm bg-light rounded-circle text-primary d-flex align-items-center justify-content-center me-3" style="width: 2.5rem; height: 2.5rem;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                                                <div class="rating small text-warning ms-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star<?php echo $i <= $testimonial['rating'] ? '' : '-o'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <p class="mb-2 text-muted small">
                                                "<?php echo strlen($testimonial['content']) > 100 ? 
                                                    htmlspecialchars(substr($testimonial['content'], 0, 100)) . '...' : 
                                                    htmlspecialchars($testimonial['content']); ?>"
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                            <?php 
                                            $date = new DateTime($testimonial['created_at']);
                                            echo $date->format('M j, Y');
                                            ?>
                                        </small>
                                        <a href="testimonial-form.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    
   
    
    <!-- System Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        System Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">PHP Version</h6>
                                <p class="mb-0"><?php echo phpversion(); ?></p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Database</h6>
                                <p class="mb-0">MySQL</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Server Software</h6>
                                <p class="mb-0"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Last Updated</h6>
                                <p class="mb-0"><?php echo date('F j, Y, g:i a'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
   
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Include the footer
require_once 'includes/footer.php';
?>
            </div>
        </div>
    </div>
    
   
<?php 
// Include the footer
require_once 'includes/footer.php';
?>
