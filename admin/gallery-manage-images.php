<?php
// Start session and include config
session_start();
require_once __DIR__ . '/../includes/config.php';

// Initialize variables
$errors = [];
$success = '';
$pdo = null;

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

// Check if gallery item ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid gallery item ID.';
    header('Location: gallery.php');
    exit();
}

$gallery_item_id = (int)$_GET['id'];

// Get gallery item details
try {
    $stmt = $pdo->prepare("
        SELECT gi.*, gc.name as category_name 
        FROM gallery_items gi
        LEFT JOIN gallery_categories gc ON gi.category_id = gc.id
        WHERE gi.id = ?
    ");
    $stmt->execute([$gallery_item_id]);
    $gallery_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$gallery_item) {
        $_SESSION['error'] = 'Gallery item not found.';
        header('Location: gallery.php');
        exit();
    }
    
    // Get all images for this gallery item
    $stmt = $pdo->prepare("
        SELECT * FROM gallery_images 
        WHERE gallery_item_id = ? 
        ORDER BY is_primary DESC, display_order, id
    
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_images'])) {
    if (!empty($_FILES['new_images']['name'][0])) {
        $uploaded_files = [];
        $total_files = count($_FILES['new_images']['name']);
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['new_images']['name'][$i],
                    'type' => $_FILES['new_images']['type'][$i],
                    'tmp_name' => $_FILES['new_images']['tmp_name'][$i],
                    'error' => $_FILES['new_images']['error'][$i],
                    'size' => $_FILES['new_images']['size'][$i]
                ];
                
                // Validate file
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                if (!in_array($file['type'], $allowed_types)) {
                    $errors[] = 'Invalid file type: ' . htmlspecialchars($file['name']);
                    continue;
                }
                
                if ($file['size'] > $max_size) {
                    $errors[] = 'File too large: ' . htmlspecialchars($file['name']) . ' (max 5MB)';
                    continue;
                }
                
                // Create upload directory if it doesn't exist
                $upload_dir = '../uploads/gallery/' . date('Y/m/');
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $uploaded_files[] = [
                        'path' => str_replace('../', '', $target_path),
                        'is_primary' => (count($images) === 0 && $i === 0) ? 1 : 0
                    ];
                } else {
                    $errors[] = 'Error uploading file: ' . htmlspecialchars($file['name']);
                }
            }
        }
        
        // Save to database if no errors
        if (empty($errors) && !empty($uploaded_files)) {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("
                    INSERT INTO gallery_images 
                    (gallery_item_id, image_path, thumbnail_path, display_order, is_primary)
                    VALUES (?, ?, ?, ?, ?)
                
                foreach ($uploaded_files as $index => $file) {
                    $display_order = count($images) + $index + 1;
                    $stmt->execute([
                        $gallery_item_id,
                        $file['path'],
                        $file['path'], // Same as image_path for now
                        $display_order,
                        $file['is_primary']
                    ]);
                }
                
                $pdo->commit();
                $success = count($uploaded_files) . ' image(s) uploaded successfully.';
                
                // Refresh the page to show new images
                header('Location: gallery-manage-images.php?id=' . $gallery_item_id);
                exit();
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = 'Database error: ' . $e->getMessage();
                
                // Clean up uploaded files if there was a database error
                foreach ($uploaded_files as $file) {
                    if (file_exists('../' . $file['path'])) {
                        unlink('../' . $file['path']);
                    }
                }
            }
        }
    } else {
        $errors[] = 'Please select at least one image to upload.';
    }
}

// Handle image actions (delete, set as primary, reorder)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['image_id']) || !is_numeric($_POST['image_id'])) {
        $errors[] = 'Invalid image ID.';
    } else {
        $image_id = (int)$_POST['image_id'];
        
        try {
            switch ($_POST['action']) {
                case 'delete':
                    // Get image path first
                    $stmt = $pdo->prepare("SELECT image_path FROM gallery_images WHERE id = ? AND gallery_item_id = ?");
                    $stmt->execute([$image_id, $gallery_item_id]);
                    $image = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($image) {
                        // Delete the file
                        if (file_exists('../' . $image['image_path'])) {
                            unlink('../' . $image['image_path']);
                        }
                        
                        // Delete the record
                        $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ? AND gallery_item_id = ?");
                        $stmt->execute([$image_id, $gallery_item_id]);
                        
                        // If this was the primary image, set another one as primary
                        $stmt = $pdo->prepare("SELECT id FROM gallery_images WHERE gallery_item_id = ? LIMIT 1");
                        $stmt->execute([$gallery_item_id]);
                        $new_primary = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($new_primary) {
                            $stmt = $pdo->prepare("UPDATE gallery_images SET is_primary = 1 WHERE id = ?");
                            $stmt->execute([$new_primary['id']]);
                        }
                        
                        $success = 'Image deleted successfully.';
                    } else {
                        $errors[] = 'Image not found or you do not have permission to delete it.';
                    }
                    break;
                    
                case 'set_primary':
                    // First, unset any existing primary image
                    $stmt = $pdo->prepare("UPDATE gallery_images SET is_primary = 0 WHERE gallery_item_id = ?");
                    $stmt->execute([$gallery_item_id]);
                    
                    // Set the new primary image
                    $stmt = $pdo->prepare("UPDATE gallery_images SET is_primary = 1 WHERE id = ? AND gallery_item_id = ?");
                    $stmt->execute([$image_id, $gallery_item_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        $success = 'Primary image updated successfully.';
                    } else {
                        $errors[] = 'Failed to update primary image. Image not found or you do not have permission.';
                    }
                    break;
                    
                case 'reorder':
                    if (!isset($_POST['new_order']) || !is_array($_POST['new_order'])) {
                        $errors[] = 'Invalid order data.';
                        break;
                    }
                    
                    $pdo->beginTransaction();
                    
                    foreach ($_POST['new_order'] as $order => $img_id) {
                        $img_id = (int)$img_id;
                        $order = (int)$order + 1; // Convert to 1-based index
                        
                        $stmt = $pdo->prepare("UPDATE gallery_images SET display_order = ? WHERE id = ? AND gallery_item_id = ?");
                        $stmt->execute([$order, $img_id, $gallery_item_id]);
                    }
                    
                    $pdo->commit();
                    $success = 'Image order updated successfully.';
                    break;
                    
                default:
                    $errors[] = 'Invalid action.';
            }
            
            // Refresh the page to show changes
            header('Location: gallery-manage-images.php?id=' . $gallery_item_id);
            exit();
            
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Include header
$page_title = 'Manage Gallery Images';
include 'includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Manage Images: <?php echo htmlspecialchars($gallery_item['title']); ?>
        </h1>
        <a href="gallery.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Gallery
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Please fix the following issues:
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload New Images</h6>
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div class="form-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="new_images" name="new_images[]" 
                                       multiple accept="image/*" required>
                                <label class="custom-file-label" for="new_images">Choose multiple images (JPG, PNG, GIF, WebP, max 5MB each)</label>
                            </div>
                            <small class="form-text text-muted">
                                First image will be used as the primary/thumbnail image if none is set.
                            </small>
                            <div id="fileList" class="mt-2 small text-muted"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Images
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gallery Images</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($images)): ?>
                        <div class="alert alert-info mb-0">No images found for this gallery item.</div>
                    <?php else: ?>
                        <div id="sortableImages" class="row">
                            <?php foreach ($images as $image): ?>
                                <div class="col-md-4 col-lg-3 mb-4 image-item" data-id="<?php echo $image['id']; ?>">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                             class="card-img-top" 
                                             alt="Gallery Image"
                                             style="height: 150px; object-fit: cover;">
                                        <div class="card-body p-2 text-center">
                                            <?php if ($image['is_primary']): ?>
                                                <span class="badge badge-success mb-2">Primary</span>
                                            <?php else: ?>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Set this image as primary?');">
                                                    <input type="hidden" name="action" value="set_primary">
                                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary mb-2" 
                                                            title="Set as Primary">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-sort"></i> 
                                                    Order: <?php echo $image['display_order']; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <form method="POST" id="reorderForm">
                            <input type="hidden" name="action" value="reorder">
                            <input type="hidden" name="new_order" id="newOrderInput">
                            <button type="submit" class="btn btn-primary btn-sm d-none" id="saveOrderBtn">
                                <i class="fas fa-save"></i> Save Order
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gallery Item Details</h6>
                </div>
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($gallery_item['title']); ?></h5>
                    
                    <?php if (!empty($gallery_item['description'])): ?>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($gallery_item['description'])); ?></p>
                    <?php endif; ?>
                    
                    <ul class="list-unstyled">
                        <?php if (!empty($gallery_item['category_name'])): ?>
                            <li class="mb-2">
                                <strong>Category:</strong> 
                                <?php echo htmlspecialchars($gallery_item['category_name']); ?>
                            </li>
                        <?php endif; ?>
                        
                        <li class="mb-2">
                            <strong>Status:</strong> 
                            <span class="badge <?php echo $gallery_item['status'] ? 'badge-success' : 'badge-secondary'; ?>">
                                <?php echo $gallery_item['status'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </li>
                        
                        <?php if ($gallery_item['is_featured']): ?>
                            <li class="mb-2">
                                <span class="badge badge-warning">
                                    <i class="fas fa-star"></i> Featured
                                </span>
                            </li>
                        <?php endif; ?>
                        
                        <li class="mb-2">
                            <strong>Display Order:</strong> <?php echo $gallery_item['display_order']; ?>
                        </li>
                        
                        <li class="mb-2">
                            <strong>Created:</strong> 
                            <?php echo date('M j, Y', strtotime($gallery_item['created_at'])); ?>
                        </li>
                        
                        <li class="mb-2">
                            <strong>Total Images:</strong> 
                            <span class="badge badge-info">
                                <?php echo count($images); ?>
                            </span>
                        </li>
                    </ul>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="gallery-edit.php?id=<?php echo $gallery_item_id; ?>" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Details
                        </a>
                        
                        <a href="gallery-view.php?id=<?php echo $gallery_item_id; ?>" 
                           class="btn btn-info btn-sm" target="_blank">
                            <i class="fas fa-eye"></i> View on Site
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="gallery-add-multiple.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle text-success"></i> Add New Gallery Item
                        </a>
                        <a href="gallery.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-images"></i> View All Gallery Items
                        </a>
                        <a href="gallery-categories.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-tags"></i> Manage Categories
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this image? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="image_id" id="deleteImageId">
                    <button type="submit" class="btn btn-danger">Delete Image</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- SortableJS for drag and drop reordering -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show selected file names
    const fileInput = document.getElementById('new_images');
    const fileList = document.getElementById('fileList');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                let fileNames = [];
                for (let i = 0; i < this.files.length; i++) {
                    fileNames.push(this.files[i].name);
                }
                fileList.textContent = 'Selected: ' + fileNames.join(', ');
            } else {
                fileList.textContent = '';
            }
        });
    }
    
    // Initialize sortable for image reordering
    const sortable = document.getElementById('sortableImages');
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    const newOrderInput = document.getElementById('newOrderInput');
    
    if (sortable) {
        new Sortable(sortable, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                // Show save button when order changes
                saveOrderBtn.classList.remove('d-none');
                
                // Update hidden input with new order
                const items = sortable.querySelectorAll('.image-item');
                const order = Array.from(items).map(item => item.getAttribute('data-id'));
                newOrderInput.value = JSON.stringify(order);
            }
        });
        
        // Set initial order
        const items = sortable.querySelectorAll('.image-item');
        const order = Array.from(items).map(item => item.getAttribute('data-id'));
        newOrderInput.value = JSON.stringify(order);
    }
    
    // Handle delete confirmation
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const imageId = button.getAttribute('data-id');
            document.getElementById('deleteImageId').value = imageId;
        });
    }
});
</script>

<style>
/* SortableJS styles */
.sortable-ghost {
    opacity: 0.5;
    background: #f8f9fa;
}

/* Custom scrollbar for image preview */
#sortableImages {
    max-height: 600px;
    overflow-y: auto;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
