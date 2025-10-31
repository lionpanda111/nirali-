<?php
// Start session and include config
session_start();
require_once __DIR__ . '/../includes/config.php';

// Initialize variables
$pdo = null;
$success = '';
$error = '';
$gallery_images = [];
$categories = [];

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

// Initialize session variables
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['success']);

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);

// Handle image deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $image_id = (int)$_GET['delete'];
    
    try {
        // First, get the image path to delete the file
        $stmt = $pdo->prepare("SELECT image_path FROM gallery_images WHERE id = ?");
        $stmt->execute([$image_id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($image) {
            // Delete the file if it exists
            if (file_exists('../' . $image['image_path'])) {
                unlink('../' . $image['image_path']);
            }
            
            // Delete the record from the database
            $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
            $stmt->execute([$image_id]);
            
            $_SESSION['success'] = 'Image deleted successfully!';
        } else {
            $_SESSION['error'] = 'Image not found.';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting image: ' . $e->getMessage();
    }
    
    header('Location: gallery.php');
    exit();
}

// Handle status toggle
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $image_id = (int)$_GET['toggle_status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE gallery_images SET status = 1 - status WHERE id = ?");
        $stmt->execute([$image_id]);
        
        $_SESSION['success'] = 'Image status updated successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error updating image status: ' . $e->getMessage();
    }
    
    header('Location: gallery.php');
    exit();
}

// Handle featured toggle
if (isset($_GET['toggle_featured']) && is_numeric($_GET['toggle_featured'])) {
    $image_id = (int)$_GET['toggle_featured'];
    
    try {
        $stmt = $pdo->prepare("UPDATE gallery_images SET is_featured = 1 - is_featured WHERE id = ?");
        $stmt->execute([$image_id]);
        
        $_SESSION['success'] = 'Featured status updated successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error updating featured status: ' . $e->getMessage();
    }
    
    header('Location: gallery.php');
    exit();
}

// Fetch gallery images
$gallery_images = [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $query = "SELECT gi.*, '' as category_name 
              FROM gallery_images gi 
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (gi.title LIKE ? OR gi.description LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $query .= " ORDER BY gi.display_order, gi.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = 'Error fetching gallery images: ' . $e->getMessage();
}

// Include the header
$page_title = 'Manage Gallery';
require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gallery Management</h1>
        <a href="gallery-add.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Image
        </a>
    </div>
    
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gallery</li>
        </ol>
    </nav>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Gallery</h6>
        </div>
        <div class="card-body">
            <form method="get" action="" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Search by title or description..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
            </form>
        </div>
    </div>

    <!-- Gallery Images -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-images me-1"></i>
                Gallery Images
            </div>
            <div class="input-group input-group-sm" style="width: 300px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search gallery..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                    <i class="fas fa-search"></i>
                </button>
                <a href="gallery.php" class="btn btn-outline-secondary" title="Reset Search">
                    <i class="fas fa-sync"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($gallery_images)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-images fa-4x mb-3 text-muted"></i>
                    <h5 class="text-muted">No gallery images found</h5>
                    <p class="text-muted">Get started by adding your first image</p>
                    <a href="gallery-add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Image
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($gallery_images as $image): ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="card h-100 shadow-sm">
                                <div class="position-relative" style="padding-top: 75%; overflow: hidden;">
                                    <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         class="position-absolute top-0 start-0 w-100 h-100" 
                                         alt="<?php echo htmlspecialchars($image['title']); ?>"
                                         style="object-fit: cover;">
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <div class="btn-group">
                                            <a href="gallery.php?toggle_status=<?php echo $image['id']; ?>" 
                                               class="btn btn-sm btn-<?php echo $image['status'] ? 'success' : 'secondary'; ?>" 
                                               title="<?php echo $image['status'] ? 'Active' : 'Inactive'; ?>"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top">
                                                <i class="fas fa-<?php echo $image['status'] ? 'check' : 'times'; ?>"></i>
                                            </a>
                                            <a href="gallery.php?toggle_featured=<?php echo $image['id']; ?>" 
                                               class="btn btn-sm btn-<?php echo $image['is_featured'] ? 'warning' : 'secondary'; ?>" 
                                               title="<?php echo $image['is_featured'] ? 'Featured' : 'Not Featured'; ?>"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top">
                                                <i class="fas fa-star"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <h6 class="card-title text-truncate mb-1" title="<?php echo htmlspecialchars($image['title']); ?>">
                                        <?php echo htmlspecialchars($image['title']); ?>
                                    </h6>
                                    <?php if (!empty($image['description'])): ?>
                                        <p class="card-text small text-muted mb-2" style="min-height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            <?php echo htmlspecialchars($image['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 pt-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo date('M j, Y', strtotime($image['created_at'])); ?>
                                        </small>
                                        <div class="btn-group">
                                            <a href="gallery-edit.php?id=<?php echo $image['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Edit"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal" 
                                                    data-id="<?php echo $image['id']; ?>"
                                                    title="Delete"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this image? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
// Handle delete confirmation and initialize components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });
    
    // Handle search button click
    const searchButton = document.getElementById('searchButton');
    const searchInput = document.getElementById('searchInput');
    
    if (searchButton && searchInput) {
        searchButton.addEventListener('click', function() {
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                window.location.href = 'gallery.php?search=' + encodeURIComponent(searchTerm);
            } else {
                window.location.href = 'gallery.php';
            }
        });
        
        // Handle Enter key in search input
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchButton.click();
            }
        });
    }
    
    // Handle delete modal
    const deleteModal = document.getElementById('deleteModal');
    const confirmDelete = document.getElementById('confirmDelete');
    
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const imageId = button.getAttribute('data-id');
            confirmDelete.href = '?delete=' + imageId;
        });
    }
});
</script>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
