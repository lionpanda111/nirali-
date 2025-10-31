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
    $stmt = $pdo->query("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM gallery_images");
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
    
    // Handle file upload
    $uploaded_file = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        }
        
        // Check file size (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            $errors[] = 'Image size must be less than 5MB.';
        }
        
        if (empty($errors)) {
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
                $uploaded_file = str_replace('../', '', $target_path);
            } else {
                $errors[] = 'Error uploading file. Please try again.';
            }
        }
    } else {
        $errors[] = 'Please select an image to upload.';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO gallery_images (
                    title, description, category_id, image_path, 
                    display_order, is_featured, status, created_at, updated_at
                ) VALUES (
                    :title, :description, :category_id, :image_path, 
                    :display_order, :is_featured, :status, NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                ':title' => $gallery['title'],
                ':description' => $gallery['description'],
                ':category_id' => $gallery['category_id'],
                ':image_path' => $uploaded_file,
                ':display_order' => $gallery['display_order'],
                ':is_featured' => $gallery['is_featured'],
                ':status' => $gallery['status']
            ]);
            
            $_SESSION['success'] = 'Image added to gallery successfully!';
            header('Location: gallery.php');
            exit();
            
        } catch (PDOException $e) {
            // Delete the uploaded file if there was a database error
            if (!empty($uploaded_file) && file_exists('../' . $uploaded_file)) {
                unlink('../' . $uploaded_file);
            }
            $errors[] = 'Error saving to database: ' . $e->getMessage();
        }
    }
}

// Include the header
$page_title = 'Add Image to Gallery';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Image to Gallery</h1>
        <a href="gallery.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Gallery
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
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
                    <h6 class="m-0 font-weight-bold text-primary">Image Details</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($gallery['title']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3"><?php echo htmlspecialchars($gallery['description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">-- Select Category --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>"
                                                <?php echo ($gallery['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" 
                                           name="display_order" value="<?php echo $gallery['display_order']; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" 
                                           name="is_featured" value="1" <?php echo $gallery['is_featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">
                                        Mark as Featured
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="status" 
                                           name="status" value="1" <?php echo $gallery['status'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="image" name="image" required 
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="form-text">Allowed formats: JPG, PNG, GIF, WebP. Max size: 5MB.</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Image
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Image Preview</h6>
                </div>
                <div class="card-body text-center">
                    <div id="imagePreview" class="border p-3 mb-3" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                        <p class="text-muted mb-0">Image preview will appear here</p>
                    </div>
                    <p class="small text-muted mb-0">
                        Recommended size: 800x600px or larger. The image will be automatically resized and optimized.
                    </p>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="small">
                        <li>Use descriptive titles that help with SEO.</li>
                        <li>Add relevant descriptions for better accessibility.</li>
                        <li>Use featured images for your portfolio highlights.</li>
                        <li>Organize images with appropriate categories.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview
const imageInput = document.getElementById('image');
const imagePreview = document.getElementById('imagePreview');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            imagePreview.innerHTML = `<img src="${e.target.result}" class="img-fluid" style="max-height: 300px;">`;
        }
        
        reader.readAsDataURL(file);
    }
});
</script>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
