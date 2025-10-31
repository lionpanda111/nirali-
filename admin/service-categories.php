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
$page_title = 'Manage Service Categories';

// Get database connection
$pdo = getDBConnection();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Check if category is in use
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_category_mapping WHERE category_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error'] = 'Cannot delete category: It is being used by one or more services.';
        } else {
            // Soft delete the category
            $stmt = $pdo->prepare("UPDATE service_categories SET status = 0, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = 'Category has been deleted successfully.';
        }
        
        header('Location: service-categories.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting category: ' . $e->getMessage();
    }
}

// Get all categories
$categories = [];
try {
    $query = "SELECT c.*, 
                     (SELECT COUNT(*) FROM service_category_mapping WHERE category_id = c.id) as service_count
              FROM service_categories c
              WHERE c.status = 1
              ORDER BY c.display_order, c.name";
    
    $categories = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error fetching categories: ' . $e->getMessage();
}

// Include the header
require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Service Categories</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="services.php">Services</a></li>
        <li class="breadcrumb-item active">Categories</li>
    </ol>
    
    <?php displayAlerts(); ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-tags me-1"></i>
            Service Categories
            <a href="category-edit.php" class="btn btn-primary btn-sm float-end">
                <i class="fas fa-plus"></i> Add New Category
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="categoriesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Services</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No categories found. <a href="category-edit.php">Add your first category</a>.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                        <div class="small text-muted">
                                            /<?php echo htmlspecialchars($category['slug']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo (int)$category['service_count']; ?> services</span>
                                    </td>
                                    <td><?php echo $category['display_order']; ?></td>
                                    <td>
                                        <?php if ($category['status'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="category-edit.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="confirmDelete(<?php echo $category['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
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
    if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
        window.location.href = 'service-categories.php?action=delete&id=' + id;
    }
    return false;
}

// Initialize DataTable
$(document).ready(function() {
    $('#categoriesTable').DataTable({
        "order": [[3, "asc"]], // Sort by display_order by default
        "columnDefs": [
            { "orderable": false, "targets": [5] } // Disable sorting on actions column
        ]
    });
});
</script>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
