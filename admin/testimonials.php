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

// Handle testimonial deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $testimonial_id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$testimonial_id]);
        
        $_SESSION['success'] = 'Testimonial deleted successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting testimonial: ' . $e->getMessage();
    }
    
    header('Location: testimonials.php');
    exit();
}

// Handle status toggle
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $testimonial_id = (int)$_GET['toggle_status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE testimonials SET status = 1 - status WHERE id = ?");
        $stmt->execute([$testimonial_id]);
        
        $_SESSION['success'] = 'Testimonial status updated successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error updating testimonial status: ' . $e->getMessage();
    }
    
    header('Location: testimonials.php');
    exit();
}

// Handle featured toggle
if (isset($_GET['toggle_featured']) && is_numeric($_GET['toggle_featured'])) {
    $testimonial_id = (int)$_GET['toggle_featured'];
    
    try {
        $stmt = $pdo->prepare("UPDATE testimonials SET is_featured = 1 - is_featured WHERE id = ?");
        $stmt->execute([$testimonial_id]);
        
        $_SESSION['success'] = 'Featured status updated successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error updating featured status: ' . $e->getMessage();
    }
    
    header('Location: testimonials.php');
    exit();
}

// Fetch all testimonials
try {
    $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY display_order, created_at DESC");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching testimonials: ' . $e->getMessage();
    error_log($error);
    $testimonials = [];
}

// Include the header
$page_title = 'Manage Testimonials';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Testimonials</h1>
        <a href="testimonial-form.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Testimonial
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Testimonials List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Testimonials</h6>
            <span><?php echo count($testimonials); ?> testimonials found</span>
        </div>
        <div class="card-body">
            <?php if (empty($testimonials)): ?>
                <div class="alert alert-info">No testimonials found. <a href="testimonial-form.php">Add your first testimonial</a>.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Content</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($testimonials as $testimonial): ?>
                                <tr>
                                    <td><?php echo $testimonial['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($testimonial['client_name']); ?></strong>
                                        <?php if (!empty($testimonial['position'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($testimonial['position']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo nl2br(htmlspecialchars(substr($testimonial['content'], 0, 100) . (strlen($testimonial['content']) > 100 ? '...' : ''))); ?></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td>
                                        <a href="?toggle_status=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-<?php echo $testimonial['status'] ? 'success' : 'secondary'; ?> btn-sm">
                                            <?php echo $testimonial['status'] ? 'Active' : 'Inactive'; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="?toggle_featured=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-<?php echo $testimonial['is_featured'] ? 'warning' : 'secondary'; ?> btn-sm">
                                            <i class="fas fa-<?php echo $testimonial['is_featured'] ? 'star' : 'star'; ?>"></i>
                                        </a>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="testimonial-form.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" class="btn btn-danger delete-testimonial" data-id="<?php echo $testimonial['id']; ?>" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this testimonial? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete testimonial confirmation
    const deleteButtons = document.querySelectorAll('.delete-testimonial');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const testimonialId = this.getAttribute('data-id');
            confirmDeleteBtn.href = `testimonials.php?delete=${testimonialId}`;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
});
</script>
