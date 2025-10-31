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
$category = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'description' => '',
    'image' => '',
    'display_order' => 0,
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => ''
];

$isEdit = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $category['id'] = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $category['name'] = trim($_POST['name'] ?? '');
    $category['slug'] = createSlug($category['name']);
    $category['description'] = trim($_POST['description'] ?? '');
    $category['display_order'] = (int)($_POST['display_order'] ?? 0);
    $category['meta_title'] = trim($_POST['meta_title'] ?? '');
    $category['meta_description'] = trim($_POST['meta_description'] ?? '');
    $category['meta_keywords'] = trim($_POST['meta_keywords'] ?? '');
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/categories/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $fileName = uniqid('category_') . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        // Check file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExt, $allowedTypes)) {
            $_SESSION['error'] = 'Only JPG, JPEG, PNG, and WebP files are allowed.';
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Delete old image if exists
            if (!empty($category['image']) && file_exists($category['image'])) {
                @unlink($category['image']);
            }
            $category['image'] = 'uploads/categories/' . $fileName;
        } else {
            $_SESSION['error'] = 'Error uploading image. Please try again.';
        }
    } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        // Remove existing image
        if (!empty($category['image']) && file_exists('../' . $category['image'])) {
            @unlink('../' . $category['image']);
        }
        $category['image'] = '';
    } elseif (!empty($category['id'])) {
        // Keep existing image if editing and no new image uploaded
        $stmt = $pdo->prepare("SELECT image FROM service_categories WHERE id = ?");
        $stmt->execute([$category['id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        $category['image'] = $existing['image'] ?? '';
    }
    
    // Validate required fields
    if (empty($category['name'])) {
        $_SESSION['error'] = 'Category name is required.';
    } else {
        try {
            if ($category['id'] > 0) {
                // Update existing category
                $stmt = $pdo->prepare(
                    "UPDATE service_categories SET 
                        name = ?, slug = ?, description = ?, image = ?,
                        display_order = ?, meta_title = ?, meta_description = ?,
                        meta_keywords = ?, updated_at = NOW()
                    WHERE id = ?"
                );
                
                $stmt->execute([
                    $category['name'], $category['slug'], $category['description'],
                    $category['image'], $category['display_order'], $category['meta_title'],
                    $category['meta_description'], $category['meta_keywords'], $category['id']
                ]);
                
                $successMessage = 'Category updated successfully.';
            } else {
                // Insert new category
                $stmt = $pdo->prepare(
                    "INSERT INTO service_categories (
                        name, slug, description, image, display_order,
                        meta_title, meta_description, meta_keywords,
                        status, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())"
                );
                
                $stmt->execute([
                    $category['name'], $category['slug'], $category['description'],
                    $category['image'], $category['display_order'], $category['meta_title'],
                    $category['meta_description'], $category['meta_keywords']
                ]);
                
                $category['id'] = $pdo->lastInsertId();
                $successMessage = 'Category added successfully.';
            }
            
            $_SESSION['success'] = $successMessage;
            header('Location: service-categories.php');
            exit();
            
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error'] = 'A category with this name already exists.';
            } else {
                $_SESSION['error'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
} elseif (isset($_GET['id'])) {
    // Load existing category for editing
    $id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM service_categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category) {
            $isEdit = true;
        } else {
            $_SESSION['error'] = 'Category not found.';
            header('Location: service-categories.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error loading category: ' . $e->getMessage();
        header('Location: service-categories.php');
        exit();
    }
}

// Set page title
$page_title = $isEdit ? 'Edit Category' : 'Add New Category';

// Include the header
require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $isEdit ? 'Edit Category' : 'Add New Category'; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="service-categories.php">Service Categories</a></li>
        <li class="breadcrumb-item active"><?php echo $isEdit ? 'Edit' : 'Add'; ?></li>
    </ol>
    
    <?php displayAlerts(); ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus'; ?> me-1"></i>
            <?php echo $isEdit ? 'Edit Category' : 'Add New Category'; ?>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Basic Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Basic Information
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Category Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a category name.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="slug" class="form-label">URL Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="<?php echo htmlspecialchars($category['slug']); ?>">
                                    <div class="form-text">Leave empty to auto-generate from name</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="4"><?php echo htmlspecialchars($category['description']); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SEO -->
                        <div class="card mb-4">
                            <div class="card-header">
                                SEO Settings
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                           value="<?php echo htmlspecialchars($category['meta_title']); ?>" 
                                           maxlength="60">
                                    <div class="form-text">Recommended: 50-60 characters</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" 
                                              rows="2" maxlength="160"><?php echo htmlspecialchars($category['meta_description']); ?></textarea>
                                    <div class="form-text">Recommended: 150-160 characters</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                           value="<?php echo htmlspecialchars($category['meta_keywords']); ?>">
                                    <div class="form-text">Comma-separated keywords</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Status & Order -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Display Settings
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" 
                                           name="display_order" value="<?php echo $category['display_order']; ?>">
                                    <div class="form-text">Lower numbers display first</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Category Image -->
                        <div class="card mb-4">
                            <div class="card-header">
                                Category Image
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($category['image']) && file_exists('../' . $category['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($category['image']); ?>" 
                                         alt="Category Image" class="img-thumbnail mb-3" 
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
                                    <label for="image" class="form-label">Upload Image</label>
                                    <input class="form-control" type="file" id="image" name="image" 
                                           accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text">Recommended size: 800x600px, Max size: 2MB</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="service-categories.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Categories
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

// Auto-generate slug from name
const nameInput = document.getElementById('name');
const slugInput = document.getElementById('slug');

if (nameInput && slugInput) {
    nameInput.addEventListener('blur', function() {
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
</script>

<?php
// Include the footer
require_once 'includes/footer.php';
?>
