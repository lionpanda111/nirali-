<?php
// Start session and include config
session_start();
require_once __DIR__ . '/../includes/config.php';

// Initialize variables
$errors = [];
$success = '';
$pdo = null;

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

// Form data
$gallery = [
    'title' => '',
    'description' => '',
    'category_id' => '',
    'display_order' => 0,
    'is_featured' => 0,
    'status' => 1
];

// Fetch categories for dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM gallery_categories WHERE status = 1 ORDER BY display_order, name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get the next display order
    $stmt = $pdo->query("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM gallery_items");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $gallery['display_order'] = $result['next_order'];
    
} catch (PDOException $e) {
    $errors[] = 'Error fetching categories: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $gallery['title'] = trim($_POST['title'] ?? '');
    $gallery['description'] = trim($_POST['description'] ?? '');
    $gallery['category_id'] = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $gallery['display_order'] = !empty($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
    $gallery['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $gallery['status'] = isset($_POST['status']) ? 1 : 0;
    
    // Validation
    if (empty($gallery['title'])) {
        $errors[] = 'Title is required.';
    }
    
    // Check if files were uploaded
    $uploaded_files = [];
    if (!empty($_FILES['images']['name'][0])) {
        $total_files = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i]
                ];
                
                // Check file type
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file['type'], $allowed_types)) {
                    $errors[] = 'Only JPG, PNG, GIF, and WebP images are allowed.';
                    continue;
                }
                
                // Check file size (max 5MB)
                $max_size = 5 * 1024 * 1024; // 5MB
                if ($file['size'] > $max_size) {
                    $errors[] = 'Image size must be less than 5MB: ' . htmlspecialchars($file['name']);
                    continue;
                }
                
                // Create uploads directory if it doesn't exist
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
                        'is_primary' => ($i === 0) ? 1 : 0
                    ];
                } else {
                    $errors[] = 'Error uploading file: ' . htmlspecialchars($file['name']);
                }
            }
        }
    } else {
        $errors[] = 'Please select at least one image to upload.';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert gallery item
            $stmt = $pdo->prepare("
                INSERT INTO gallery_items 
                (title, description, category_id, is_featured, display_order, status)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $gallery['title'],
                $gallery['description'],
                $gallery['category_id'],
                $gallery['is_featured'],
                $gallery['display_order'],
                $gallery['status']
            ]);
            
            $gallery_item_id = $pdo->lastInsertId();
            
            // Insert images
            $stmt = $pdo->prepare("
                INSERT INTO gallery_images 
                (gallery_item_id, image_path, thumbnail_path, display_order, is_primary)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($uploaded_files as $index => $file) {
                // For now, we'll use the same path for thumbnail
                // You can add thumbnail generation logic here if needed
                $stmt->execute([
                    $gallery_item_id,
                    $file['path'],
                    $file['path'], // Same as image_path for now
                    $index + 1,
                    $file['is_primary']
                ]);
            }
            
            $pdo->commit();
            
            $_SESSION['success'] = 'Gallery item with ' . count($uploaded_files) . ' images has been added successfully.';
            header('Location: gallery.php');
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
}

// Include header
$page_title = 'Add Multiple Images to Gallery';
include 'includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Multiple Images to Gallery</h1>
        <a href="gallery.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Gallery
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Error!</strong> Please fix the following issues:
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gallery Item Details</h6>
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="galleryForm">
                        <div class="form-group">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($gallery['title']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo htmlspecialchars($gallery['description']); 
                            ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" name="category_id">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                        <?php echo ($gallery['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Images <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="images" name="images[]" 
                                       multiple accept="image/*" required>
                                <label class="custom-file-label" for="images">Choose multiple images (JPG, PNG, GIF, WebP, max 5MB each)</label>
                            </div>
                            <small class="form-text text-muted">
                                First image will be used as the primary/thumbnail image.
                            </small>
                            <div id="imagePreview" class="row mt-3"></div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="display_order">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order"
                                       value="<?php echo $gallery['display_order']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured"
                                    <?php echo $gallery['is_featured'] ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="is_featured">Featured Item</label>
                            </div>
                            
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="status" name="status"
                                    <?php echo $gallery['status'] ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="status">Active</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Gallery Item</button>
                        <a href="gallery.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gallery Tips</h6>
                </div>
                <div class="card-body">
                    <p><strong>Image Guidelines:</strong></p>
                    <ul>
                        <li>Upload high-quality images (recommended: 1200x800px or larger)</li>
                        <li>Use JPG format for photographs, PNG for graphics with transparency</li>
                        <li>Keep file sizes under 5MB per image</li>
                        <li>First image will be used as the primary/thumbnail image</li>
                    </ul>
                    
                    <p class="mt-4"><strong>Categories:</strong></p>
                    <p>Organize your gallery by selecting appropriate categories. Create new categories if needed.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Image Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalPreviewImage" class="img-fluid" alt="Preview">
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Image preview functionality
document.addEventListener('DOMContentLoaded', function() {
    // Preview selected images
    const imageInput = document.getElementById('images');
    const imagePreview = document.getElementById('imagePreview');
    
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            imagePreview.innerHTML = '';
            
            if (this.files.length > 0) {
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const col = document.createElement('div');
                            col.className = 'col-6 col-md-4 col-lg-3 mb-3';
                            
                            col.innerHTML = `
                                <div class="position-relative">
                                    <img src="${e.target.result}" class="img-fluid rounded border" 
                                         style="height: 120px; width: 100%; object-fit: cover; cursor: pointer;"
                                         onclick="previewImage('${e.target.result}')">
                                    ${i === 0 ? '<span class="badge badge-success position-absolute" style="top: 5px; left: 5px;">Primary</span>' : ''}
                                </div>
                            `;
                            
                            imagePreview.appendChild(col);
                        };
                        
                        reader.readAsDataURL(file);
                    }
                }
                
                // Update file input label
                const label = this.nextElementSibling;
                label.textContent = `${this.files.length} file(s) selected`;
            }
        });
    }
});

// Function to show image in modal
function previewImage(src) {
    const modal = document.getElementById('imagePreviewModal');
    const modalImg = document.getElementById('modalPreviewImage');
    
    if (modal && modalImg) {
        modalImg.src = src;
        $(modal).modal('show');
    }
}
</script>
