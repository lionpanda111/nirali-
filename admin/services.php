<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database configuration
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in and is admin
checkAdminAccess();

// Set page title
$page_title = 'Manage Services';

// Get database connection
$pdo = getDBConnection();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // First, delete from service_category_mapping to avoid foreign key constraint
        $pdo->prepare("DELETE FROM service_category_mapping WHERE service_id = ?")->execute([$id]);
        
        // Then delete the service
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        
        // Commit the transaction
        $pdo->commit();
        
        $_SESSION['success'] = 'Service has been deleted successfully.';
        
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $pdo->rollBack();
        
        // Check if this is a foreign key constraint error
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            // Try soft delete if hard delete fails due to other constraints
            try {
                $pdo->prepare("UPDATE services SET status = 0, updated_at = NOW() WHERE id = ?")->execute([$id]);
                $_SESSION['success'] = 'Service has been deactivated successfully.';
            } catch (PDOException $e2) {
                $_SESSION['error'] = 'Error deactivating service: ' . $e2->getMessage();
            }
        } else {
            $_SESSION['error'] = 'Error deleting service: ' . $e->getMessage();
        }
    }
    
    header('Location: services.php');
    exit();
}

// Get all services with their categories
$services = [];
try {
    $query = "SELECT s.*, 
                     GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names
              FROM services s
              LEFT JOIN service_category_mapping m ON s.id = m.service_id
              LEFT JOIN service_categories c ON m.category_id = c.id
              WHERE s.status = 1
              GROUP BY s.id
              ORDER BY s.display_order, s.title";
    
    $services = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error fetching services: ' . $e->getMessage();
}

// Include the header
require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Services</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Services</li>
    </ol>
    
    <?php displayAlerts(); ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-spa me-1"></i>
            Services List
            <a href="service-edit.php" class="btn btn-primary btn-sm float-end">
                <i class="fas fa-plus"></i> Add New Service
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="servicesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Categories</th>
                            <th>Duration</th>
                            <th>Featured</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($services)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No services found. <a href="service-edit.php">Add your first service</a>.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo $service['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($service['title']); ?></strong>
                                        <div class="small text-muted">
                                            /<?php echo htmlspecialchars($service['slug']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo !empty($service['category_names']) ? htmlspecialchars($service['category_names']) : '<span class="text-muted">Uncategorized</span>'; ?></td>
                                    <td><?php echo formatDuration($service['duration'] ?? 0); ?></td>
                                    <td>
                                        <?php if ($service['is_featured']): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $service['display_order']; ?></td>
                                    <td>
                                        <a href="service-edit.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="confirmDelete(<?php echo $service['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
        window.location.href = 'services.php?action=delete&id=' + id;
    }
    return false;
}

// Initialize DataTable
$(document).ready(function() {
    $('#servicesTable').DataTable({
        "order": [[6, "asc"]], // Sort by display_order by default
        "columnDefs": [
            { "orderable": false, "targets": [7] } // Disable sorting on actions column
        ]
    });
});
</script>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
