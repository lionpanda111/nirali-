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

// Get database connection
$pdo = getDBConnection();

// Initialize variables
$service = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'description' => '',
    'short_description' => '',
    'duration' => 60,
    'image' => '',
    'is_featured' => 0,
    'display_order' => 0,
    'status' => 1,
    'category_ids' => []
];

$isEdit = false;

// Get all categories for the form
$categories = [];
try {
    $categories = $pdo->query(
        "SELECT id, name FROM service_categories 
         WHERE status = 1 
         ORDER BY display_order, name"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error loading categories: ' . $e->getMessage();
    header('Location: services.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $service['id'] = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $service['title'] = trim($_POST['title'] ?? '');
    $service['slug'] = createSlug($service['title']);
    $service['description'] = trim($_POST['description'] ?? '');
    $service['short_description'] = trim($_POST['short_description'] ?? '');
    $service['duration'] = (int)($_POST['duration'] ?? 60);
    $service['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $service['display_order'] = (int)($_POST['display_order'] ?? 0);
    $service['status'] = 1;
    $category_ids = isset($_POST['category_ids']) ? (array)$_POST['category_ids'] : [];
    $category_ids = array_map('intval', $category_ids);
    
    // Handle main image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/services/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $fileName = uniqid('service_') . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        // Check file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExt, $allowedTypes)) {
            $_SESSION['error'] = 'Only JPG, JPEG, PNG, and WebP files are allowed.';
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Delete old image if exists
            if (!empty($service['image']) && file_exists('../' . $service['image'])) {
                @unlink('../' . $service['image']);
            }
            $service['image'] = 'uploads/services/' . $fileName;
        } else {
            $_SESSION['error'] = 'Error uploading image. Please try again.';
        }
    } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        // Remove existing image
        if (!empty($service['image']) && file_exists('../' . $service['image'])) {
            @unlink('../' . $service['image']);
        }
        $service['image'] = '';
    } elseif (!empty($service['id'])) {
        // Keep existing image if editing and no new image uploaded
        $stmt = $pdo->prepare("SELECT image FROM services WHERE id = ?");
        $stmt->execute([$service['id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        $service['image'] = $existing['image'] ?? '';
    }
    
    // Handle gallery images upload
    if (!empty($_FILES['gallery_images']['name'][0])) {
        $uploadDir = '../uploads/services/gallery/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        $maxFiles = 10;
        $uploadedCount = 0;
        
        // Count existing images to enforce max limit
        if (!empty($service['id'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_images WHERE service_id = ?");
            $stmt->execute([$service['id']]);
            $existingCount = (int)$stmt->fetchColumn();
            $maxFiles -= $existingCount;
        }
        
        if ($maxFiles <= 0) {
            $_SESSION['error'] = 'Maximum number of gallery images (10) reached. Please delete some images before uploading more.';
        } else {
            $files = $_FILES['gallery_images'];
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount && $uploadedCount < $maxFiles; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $fileExt = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    
                    if (in_array($fileExt, $allowedTypes) && $files['size'][$i] <= 2 * 1024 * 1024) {
                        $fileName = uniqid('gallery_') . '.' . $fileExt;
                        $targetPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                            // Insert into service_images table
                            $stmt = $pdo->prepare(
                                "INSERT INTO service_images (service_id, image_path, display_order) 
                                 VALUES (?, ?, ?)"
                            );
                            $stmt->execute([
                                $service['id'] ?: $pdo->lastInsertId(),
                                'uploads/services/gallery/' . $fileName,
                                $i + 1
                            ]);
                            
                            $uploadedCount++;
                        }
                    }
                }
            }
            
            if ($uploadedCount > 0) {
                $_SESSION['success'] = "$uploadedCount image(s) uploaded successfully.";
            }
        }
    }
    
    // Validate required fields
    if (empty($service['title'])) {
        $_SESSION['error'] = 'Title is required.';
    } elseif (empty($category_ids)) {
        $_SESSION['error'] = 'Please select at least one category.';
    } else {
        try {
            $pdo->beginTransaction();
            
            if ($service['id'] > 0) {
                // Update existing service
                $stmt = $pdo->prepare(
                    "UPDATE services SET \n                        title = ?, slug = ?, description = ?, short_description = ?,\n                        duration = ?, image = ?, is_featured = ?,\n                        display_order = ?, status = ?, updated_at = NOW()\n                    WHERE id = ?"
                );
                
                $stmt->execute([
                    $service['title'], $service['slug'], $service['description'],
                    $service['short_description'], $service['duration'],
                    $service['image'], $service['is_featured'], $service['display_order'],
                    $service['status'], $service['id']
                ]);
                
                // Remove existing category mappings
                $pdo->prepare("DELETE FROM service_category_mapping WHERE service_id = ?")
                    ->execute([$service['id']]);
                
                $successMessage = 'Service updated successfully.';
            } else {
                // Insert new service
                $stmt = $pdo->prepare(
                    "INSERT INTO services (\n                        title, slug, description, short_description, duration,\n                        image, is_featured, display_order, status, created_at, updated_at\n                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
                );
                
                $stmt->execute([
                    $service['title'], $service['slug'], $service['description'],
                    $service['short_description'], $service['duration'],
                    $service['image'], $service['is_featured'], $service['display_order'],
                    $service['status']
                ]);
                
                $service['id'] = $pdo->lastInsertId();
                $successMessage = 'Service added successfully.';
            }
            
            // Add category mappings
            $stmt = $pdo->prepare(
                "INSERT INTO service_category_mapping (service_id, category_id, created_at) 
                 VALUES (?, ?, NOW())"
            );
            
            foreach ($category_ids as $category_id) {
                $stmt->execute([$service['id'], $category_id]);
            }
            
            $pdo->commit();
            
            $_SESSION['success'] = $successMessage;
            header('Location: services.php');
            exit();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
    }
} elseif (isset($_GET['id'])) {
    // Load existing service for editing
    $id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($service) {
            $isEdit = true;
            
            // Get category mappings
            $stmt = $pdo->prepare(
                "SELECT category_id FROM service_category_mapping 
                 WHERE service_id = ?"
            );
            $stmt->execute([$id]);
            $service['category_ids'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Get service images
            $stmt = $pdo->prepare(
                "SELECT * FROM service_images 
                 WHERE service_id = ? 
                 ORDER BY display_order, created_at"
            );
            $stmt->execute([$id]);
            $service['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $_SESSION['error'] = 'Service not found.';
            header('Location: services.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error loading service: ' . $e->getMessage();
        header('Location: services.php');
        exit();
    }
}

// Set page title
$page_title = $isEdit ? 'Edit Service' : 'Add New Service';

// Include the header
require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $isEdit ? 'Edit Service' : 'Add New Service'; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="services.php">Services</a></li>
        <li class="breadcrumb-item active"><?php echo $isEdit ? 'Edit' : 'Add'; ?></li>
    </ol>
    
    <?php displayAlerts(); ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus'; ?> me-1"></i>
            <?php echo $isEdit ? 'Edit Service' : 'Add New Service'; ?>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Basic Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Basic Information
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Service Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($service['title'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a title for the service.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="slug" class="form-label">URL Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="<?php echo htmlspecialchars($service['slug'] ?? ''); ?>">
                                    <div class="form-text">Leave empty to auto-generate from title</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="short_description" class="form-label">Short Description</label>
                                    <textarea class="form-control" id="short_description" name="short_description" 
                                              rows="2" maxlength="255"><?php echo htmlspecialchars($service['short_description'] ?? ''); ?></textarea>
                                    <div class="form-text">A brief description (max 255 characters)</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Full Description *</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="8" required><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
                                    <div class="form-text">You can use simple HTML tags like &lt;b&gt;, &lt;i&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;br&gt;, etc.</div>
                                    <div class="invalid-feedback">
                                        Please provide a detailed description of the service.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Categories -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Categories *
                            </div>
                            <div class="card-body">
                                <?php if (empty($categories)): ?>
                                    <div class="alert alert-warning">
                                        No categories found. <a href="category-edit.php">Create a category</a> first.
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($categories as $category): ?>
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="category_ids[]" 
                                                           value="<?php echo $category['id']; ?>"
                                                           id="category_<?php echo $category['id']; ?>"
                                                           <?php echo in_array($category['id'], $service['category_ids']) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="category_<?php echo $category['id']; ?>">
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Pricing & Duration -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Pricing & Duration
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="duration" class="form-label">Duration (minutes)</label>
                                        <input type="number" class="form-control" id="duration" name="duration" 
                                               value="<?php echo $service['duration']; ?>" min="1" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    
                    <div class="col-md-4">
                        <!-- Status & Visibility -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Status & Visibility
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" 
                                               id="status" name="status" 
                                               value="1" <?php echo ($service['status'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="status">
                                            Active
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_featured" name="is_featured" 
                                               value="1" <?php echo $service['is_featured'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Featured Service
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" 
                                           name="display_order" value="<?php echo $service['display_order']; ?>">
                                    <div class="form-text">Lower numbers display first</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Featured Image -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Featured Image
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($service['image']) && file_exists('../' . $service['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($service['image'] ?? ''); ?>" 
                                         alt="Service Image" class="img-thumbnail mb-3" 
                                         style="max-height: 200px; width: auto;">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" 
                                               id="remove_image" name="remove_image" value="1">
                                        <label class="form-check-label" for="remove_image">
                                            Remove Image
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-light p-5 mb-3 text-center">
                                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No image selected</p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Main Image</label>
                                    <input class="form-control" type="file" id="image" name="image" 
                                           accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text">Recommended size: 800x600px, Max size: 2MB</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="gallery_images" class="form-label">Additional Images</label>
                                    <input class="form-control" type="file" id="gallery_images" 
                                           name="gallery_images[]" multiple 
                                           accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text">Select multiple images (max 10, 2MB each)</div>
                                </div>
                                
                                <?php if (!empty($service['images'])): ?>
                                <div class="gallery-images mt-3">
                                    <h6>Gallery Images</h6>
                                    <div class="row">
                                        <?php foreach ($service['images'] as $image): ?>
                                        <div class="col-md-4 mb-3 image-item" data-id="<?php echo $image['id']; ?>">
                                            <div class="card">
                                                <img src="../<?php echo htmlspecialchars($image['image_path'] ?? ''); ?>" 
                                                     class="card-img-top" alt="Gallery Image">
                                                <div class="card-body p-2 text-center">
                                                    <button type="button" class="btn btn-sm btn-danger delete-image" 
                                                            data-id="<?php echo $image['id']; ?>"
                                                            data-path="<?php echo htmlspecialchars($image['image_path']); ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="services.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Services
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Style for the description textarea */
#description {
    min-height: 200px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
}
</style>

<script>
// Initialize form validation
(function () {
    'use strict'
    
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            
            form.classList.add('was-validated')
        }, false)
    })
})()

// Auto-generate slug from title
const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');

if (titleInput && slugInput) {
    titleInput.addEventListener('blur', function() {
        if (!slugInput.value) {
            const slug = createSlug(this.value);
            slugInput.value = slug;
        }
    });
}

function createSlug(text) {
    return text.toLowerCase()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-')      // Replace spaces with -
        .replace(/--+/g, '-')      // Replace multiple - with single -
        .trim();
}

// Make the description textarea auto-resize
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('description');
    if (textarea) {
        // Auto-resize the textarea as the user types
        function autoResize() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        }
        
        textarea.addEventListener('input', autoResize);
        // Trigger resize on page load if there's content
        if (textarea.value) {
            autoResize.call(textarea);
        }
    }
    
    // Handle delete image button clicks
    document.querySelectorAll('.delete-image').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this image?')) {
                const imageId = this.dataset.id;
                const imagePath = this.dataset.path;
                const imageItem = this.closest('.image-item');
                
                // Send AJAX request to delete the image
                const formData = new FormData();
                formData.append('action', 'delete_service_image');
                formData.append('image_id', imageId);
                formData.append('image_path', imagePath);
                
                fetch('ajax-handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the image element from the DOM
                        imageItem.remove();
                        showAlert('success', 'Image deleted successfully.');
                    } else {
                        showAlert('danger', data.message || 'Error deleting image.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred while deleting the image.');
                });
            }
        });
    });
    
    // Show alert message
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('.container-fluid');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-remove alert after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
</script>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
