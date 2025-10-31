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

// Initialize variables
$testimonial = [
    'id' => '',
    'client_name' => '',
    'client_image' => '',
    'position' => '',
    'content' => '',
    'rating' => 5,
    'is_featured' => 0,
    'display_order' => 0,
    'status' => 1
];

$is_edit = false;
$page_title = 'Add New Testimonial';
$errors = [];

// Check if editing an existing testimonial
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $testimonial_id = (int)$_GET['id'];
    $is_edit = true;
    $page_title = 'Edit Testimonial';
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->execute([$testimonial_id]);
        $testimonial_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($testimonial_data) {
            $testimonial = array_merge($testimonial, $testimonial_data);
        } else {
            $_SESSION['error'] = 'Testimonial not found!';
            header('Location: testimonials.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error fetching testimonial: ' . $e->getMessage();
        header('Location: testimonials.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $testimonial['client_name'] = trim($_POST['client_name'] ?? '');
    $testimonial['position'] = trim($_POST['position'] ?? '');
    $testimonial['content'] = trim($_POST['content'] ?? '');
    $testimonial['rating'] = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $testimonial['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $testimonial['display_order'] = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
    $testimonial['status'] = isset($_POST['status']) ? 1 : 0;
    
    // Validate input
    if (empty($testimonial['client_name'])) {
        $errors[] = 'Client name is required';
    }
    
    if (empty($testimonial['content'])) {
        $errors[] = 'Testimonial content is required';
    }
    
    if ($testimonial['rating'] < 1 || $testimonial['rating'] > 5) {
        $testimonial['rating'] = 5; // Default to 5 if invalid
    }
    
    // Handle file upload if a new image is provided
    if (isset($_FILES['client_image']) && $_FILES['client_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/testimonials/';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['client_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid('testimonial_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['client_image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists and we're editing
                if ($is_edit && !empty($testimonial['client_image']) && file_exists('../' . $testimonial['client_image'])) {
                    unlink('../' . $testimonial['client_image']);
                }
                
                $testimonial['client_image'] = 'uploads/testimonials/' . $new_filename;
            } else {
                $errors[] = 'Error uploading image. Please try again.';
            }
        } else {
            $errors[] = 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.';
        }
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            if ($is_edit) {
                // Update existing testimonial
                $sql = "UPDATE testimonials SET 
                        client_name = :client_name,
                        position = :position,
                        content = :content,
                        rating = :rating,
                        is_featured = :is_featured,
                        display_order = :display_order,
                        status = :status" . 
                        (!empty($testimonial['client_image']) ? ", client_image = :client_image" : "") . 
                        " WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $testimonial['id'], PDO::PARAM_INT);
            } else {
                // Insert new testimonial
                $sql = "INSERT INTO testimonials (
                            client_name, position, content, rating, " . 
                            (!empty($testimonial['client_image']) ? "client_image, " : "") . 
                            "is_featured, display_order, status, created_at
                        ) VALUES (
                            :client_name, :position, :content, :rating, " . 
                            (!empty($testimonial['client_image']) ? ":client_image, " : "") . 
                            ":is_featured, :display_order, :status, NOW()
                        )";
                
                $stmt = $pdo->prepare($sql);
            }
            
            // Bind parameters
            $stmt->bindParam(':client_name', $testimonial['client_name']);
            $stmt->bindParam(':position', $testimonial['position']);
            $stmt->bindParam(':content', $testimonial['content']);
            $stmt->bindParam(':rating', $testimonial['rating'], PDO::PARAM_INT);
            $stmt->bindParam(':is_featured', $testimonial['is_featured'], PDO::PARAM_INT);
            $stmt->bindParam(':display_order', $testimonial['display_order'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $testimonial['status'], PDO::PARAM_INT);
            
            if (!empty($testimonial['client_image'])) {
                $stmt->bindParam(':client_image', $testimonial['client_image']);
            }
            
            $stmt->execute();
            
            if (!$is_edit) {
                $testimonial['id'] = $pdo->lastInsertId();
            }
            
            $_SESSION['success'] = 'Testimonial ' . ($is_edit ? 'updated' : 'added') . ' successfully!';
            header('Location: testimonials.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            error_log('Testimonial save error: ' . $e->getMessage());
        }
    }
}

// Include the header
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
        <a href="testimonials.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Testimonials
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

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="client_name" class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="client_name" name="client_name" 
                                   value="<?php echo htmlspecialchars($testimonial['client_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="position" class="form-label">Position/Title</label>
                            <input type="text" class="form-control" id="position" name="position" 
                                   value="<?php echo htmlspecialchars($testimonial['position']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Testimonial Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="6" required><?php echo htmlspecialchars($testimonial['content']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" 
                                           <?php echo ($testimonial['rating'] == $i) ? 'checked' : ''; ?> required>
                                    <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Featured Image</h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <?php if (!empty($testimonial['client_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($testimonial['client_image']); ?>" 
                                             class="img-fluid mb-2" style="max-height: 200px;" alt="Client Image">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image">
                                            <label class="form-check-label" for="remove_image">
                                                Remove image
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="client_image" class="form-label">Upload New Image</label>
                                        <input class="form-control" type="file" id="client_image" name="client_image" accept="image/*">
                                        <small class="form-text text-muted">Recommended size: 200x200px, Max size: 2MB</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" 
                                           value="<?php echo (int)$testimonial['display_order']; ?>" min="0">
                                    <small class="form-text text-muted">Lower numbers display first</small>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                           value="1" <?php echo $testimonial['is_featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">Featured Testimonial</label>
                                </div>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" 
                                           value="1" <?php echo $testimonial['status'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Active</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Testimonial
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}
.rating > input {
    display: none;
}
.rating > label {
    position: relative;
    width: 1.5em;
    font-size: 2rem;
    color: #ffd700;
    cursor: pointer;
}
.rating > label::before {
    content: "\2605";
    position: absolute;
    opacity: 0;
}
.rating > label:hover:before,
.rating > label:hover ~ label:before,
.rating > input:checked ~ label:before {
    opacity: 1;
}
</style>

<?php require_once 'includes/footer.php'; ?>
