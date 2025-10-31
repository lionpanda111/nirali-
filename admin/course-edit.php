<?php
require_once __DIR__ . '/../includes/config.php';
require_once 'includes/header.php';

// Get PDO connection
try {
    $pdo = getDBConnection();
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $page_title = $id ? 'Edit Course' : 'Add New Course';

    // Initialize variables
    $course = [
        'id' => 0,
        'title' => '',
        'slug' => '',
        'description' => '',
        'short_description' => '',
        'duration' => '',
        'price' => '',
        'image' => '',
        'is_featured' => 0,
        'display_order' => 0,
        'status' => 1
    ];

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate input
        $title = trim($_POST['title']);
        $slug = createSlug($_POST['slug'] ?: $title);
        $description = trim($_POST['description']);
        $short_description = trim($_POST['short_description']);
        $duration = trim($_POST['duration']);
        $price = (float)$_POST['price'];
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $display_order = (int)$_POST['display_order'];
        $status = isset($_POST['status']) ? 1 : 0;
        
        // Handle image upload
        $image_path = $_POST['existing_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/courses/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = 'course_' . time() . '_' . uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    // Delete old image if exists
                    if (!empty($image_path) && file_exists('../../' . $image_path)) {
                        unlink('../../' . $image_path);
                    }
                    $image_path = 'uploads/courses/' . $new_filename;
                }
            }
        }
        
        // Update or insert course
        if ($id > 0) {
            // Update existing course
            $stmt = $pdo->prepare("UPDATE courses SET 
                title = :title, slug = :slug, description = :description, 
                short_description = :short_description, duration = :duration, 
                price = :price, image = :image, is_featured = :is_featured, 
                display_order = :display_order, status = :status, 
                updated_at = NOW() 
                WHERE id = :id");
                
            $result = $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':description' => $description,
                ':short_description' => $short_description,
                ':duration' => $duration,
                ':price' => $price,
                ':image' => $image_path,
                ':is_featured' => $is_featured,
                ':display_order' => $display_order,
                ':status' => $status,
                ':id' => $id
            ]);
            
            if ($result) {
                $_SESSION['success'] = "Course updated successfully";
                header("Location: course-edit.php?id=$id");
                exit();
            } else {
                $error = "Error updating course";
            }
        } else {
            // Insert new course
            $stmt = $pdo->prepare("INSERT INTO courses 
                (title, slug, description, short_description, duration, 
                 price, image, is_featured, display_order, status, created_at, updated_at) 
                VALUES (:title, :slug, :description, :short_description, :duration, 
                        :price, :image, :is_featured, :display_order, :status, NOW(), NOW())");
                        
            $result = $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':description' => $description,
                ':short_description' => $short_description,
                ':duration' => $duration,
                ':price' => $price,
                ':image' => $image_path,
                ':is_featured' => $is_featured,
                ':display_order' => $display_order,
                ':status' => $status
            ]);
            
            if ($result) {
                $id = $pdo->lastInsertId();
                $_SESSION['success'] = "Course added successfully";
                header("Location: course-edit.php?id=$id");
                exit();
            } else {
                $error = "Error adding course";
            }
        }
    }

    // Load course data if editing
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$course) {
            $_SESSION['error'] = "Course not found";
            header('Location: courses.php');
            exit();
        }
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= $page_title ?></h1>
        <a href="courses.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Course Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($course['title']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">URL Slug *</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= SITE_URL ?>/academy/</span>
                                <input type="text" class="form-control" id="slug" name="slug" 
                                       value="<?= htmlspecialchars($course['slug']) ?>" required>
                            </div>
                            <small class="text-muted">Leave empty to auto-generate from title</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="short_description" class="form-label">Short Description *</label>
                            <textarea class="form-control" id="short_description" name="short_description" 
                                     rows="3" maxlength="500" required><?= htmlspecialchars($course['short_description']) ?></textarea>
                            <small class="text-muted">A brief summary (max 500 characters)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Full Description *</label>
                            <textarea class="form-control tinymce" id="description" name="description" 
                                     rows="10"><?= htmlspecialchars($course['description']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Course Image</h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <?php if (!empty($course['image']) && file_exists('../../' . $course['image'])): ?>
                                        <img src="../../<?= htmlspecialchars($course['image']) ?>" 
                                             alt="Course Image" class="img-fluid mb-2" style="max-height: 200px;">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 100%; height: 200px; border: 1px dashed #ccc; border-radius: 4px;">
                                            <div class="text-center">
                                                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                                <p class="mb-0">No image selected</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="image" class="form-label">Upload New Image</label>
                                    <input class="form-control form-control-sm" type="file" id="image" name="image" 
                                           accept="image/*">
                                    <small class="text-muted">Recommended size: 800x600px</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Course Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">Duration</label>
                                    <input type="text" class="form-control" id="duration" name="duration" 
                                           value="<?= htmlspecialchars($course['duration']) ?>" 
                                           placeholder="e.g., 4 weeks, 2 months">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price ($)</label>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           step="0.01" min="0" value="<?= $course['price'] > 0 ? $course['price'] : '' ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" 
                                           value="<?= $course['display_order'] ?>">
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                           value="1" <?= $course['is_featured'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_featured">Featured Course</label>
                                </div>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" 
                                           value="1" <?= $course['status'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="status">Active</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Course
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-generate slug from title
const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');

if (titleInput && slugInput) {
    titleInput.addEventListener('input', function() {
        if (!slugInput.value) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special chars
                .replace(/\s+/g, '-')      // Replace spaces with -
                .replace(/--+/g, '-');      // Replace multiple - with single -
        }
    });
}

// Initialize TinyMCE
tinymce.init({
    selector: '.tinymce',
    height: 400,
    plugins: 'link lists table image code media codesample',
    toolbar: 'undo redo | formatselect | bold italic backcolor | \
              alignleft aligncenter alignright alignjustify | \
              bullist numlist outdent indent | removeformat | help',
    menubar: false,
    statusbar: false,
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; font-size: 14px; }',
    relative_urls: false,
    convert_urls: false
});
</script>

<?php require_once 'includes/footer.php'; ?>
