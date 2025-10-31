<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Add New Blog Post';
$active_menu = 'blog';

try {
    $db = getDBConnection();
    
    // Initialize variables
    $post = [
        'id' => '',
        'title' => '',
        'slug' => '',
        'content' => '',
        'excerpt' => '',
        'featured_image' => '',
        'status' => 'draft',
        'published_at' => date('Y-m-d\TH:i'),
        'categories' => []
    ];
    
    $is_edit = false;
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate input
        $title = trim($_POST['title'] ?? '');
        $slug = createSlug($title);
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';
        $published_at = !empty($_POST['published_at']) ? date('Y-m-d H:i:s', strtotime($_POST['published_at'])) : null;
        $categories = !empty($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
        
        // Handle featured image upload
        $featured_image = $post['featured_image'];
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/blog/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
            $filename = 'blog-' . time() . '.' . strtolower($file_extension);
            $target_path = $upload_dir . $filename;
            
            // Check if the file is an actual image
            $check = getimagesize($_FILES['featured_image']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_path)) {
                    // Delete old image if exists and it's an edit
                    if ($is_edit && !empty($post['featured_image']) && file_exists($upload_dir . $post['featured_image'])) {
                        unlink($upload_dir . $post['featured_image']);
                    }
                    $featured_image = $filename;
                }
            }
        }
        
        // Validate required fields
        if (empty($title) || empty($content)) {
            $error = 'Title and content are required.';
        } else {
            try {
                $db->beginTransaction();
                
                if (!empty($_POST['id'])) {
                    // Update existing post
                    $stmt = $db->prepare("UPDATE blog_posts SET 
                        title = :title,
                        slug = :slug,
                        content = :content,
                        excerpt = :excerpt,
                        featured_image = :featured_image,
                        status = :status,
                        published_at = :published_at,
                        updated_at = NOW()
                        WHERE id = :id");
                    
                    $stmt->execute([
                        'title' => $title,
                        'slug' => $slug,
                        'content' => $content,
                        'excerpt' => $excerpt,
                        'featured_image' => $featured_image,
                        'status' => $status,
                        'published_at' => $published_at,
                        'id' => $_POST['id']
                    ]);
                    
                    $post_id = $_POST['id'];
                    $message = 'Post updated successfully!';
                } else {
                    // Insert new post
                    $stmt = $db->prepare("INSERT INTO blog_posts 
                        (title, slug, content, excerpt, featured_image, status, published_at, created_at, updated_at) 
                        VALUES (:title, :slug, :content, :excerpt, :featured_image, :status, :published_at, NOW(), NOW())");
                    
                    $stmt->execute([
                        'title' => $title,
                        'slug' => $slug,
                        'content' => $content,
                        'excerpt' => $excerpt,
                        'featured_image' => $featured_image,
                        'status' => $status,
                        'published_at' => $published_at
                    ]);
                    
                    $post_id = $db->lastInsertId();
                    $message = 'Post created successfully!';
                }
                
                // Update post categories
                if ($post_id) {
                    // Remove existing categories
                    $db->prepare("DELETE FROM blog_post_categories WHERE post_id = ?")->execute([$post_id]);
                    
                    // Add new categories
                    if (!empty($categories)) {
                        $stmt = $db->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)");
                        foreach ($categories as $category_id) {
                            $stmt->execute([$post_id, $category_id]);
                        }
                    }
                }
                
                $db->commit();
                
                $_SESSION['success_message'] = $message;
                header('Location: blog.php');
                exit();
                
            } catch (PDOException $e) {
                $db->rollBack();
                $error = 'Error saving post: ' . $e->getMessage();
            }
        }
    } 
    // Load post data if editing
    elseif (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post) {
            $is_edit = true;
            $page_title = 'Edit Blog Post';
            
            // Get post categories
            $stmt = $db->prepare("SELECT category_id FROM blog_post_categories WHERE post_id = ?");
            $stmt->execute([$post['id']]);
            $post['categories'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            header('Location: blog.php');
            exit();
        }
    }
    
    // Get all categories for the form
    $categories = $db->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Function to create URL-friendly slug
function createSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $page_title; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="blog.php">Blog Posts</a></li>
        <li class="breadcrumb-item active"><?php echo $is_edit ? 'Edit' : 'Add New'; ?></li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            <?php echo $is_edit ? 'Edit' : 'Add New'; ?> Blog Post
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data" id="blogForm">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($post['id']); ?>">
                
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($post['title']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">URL Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" 
                                   value="<?php echo htmlspecialchars($post['slug']); ?>">
                            <div class="form-text">Leave blank to auto-generate from title</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php 
                                echo htmlspecialchars($post['excerpt']); 
                            ?></textarea>
                            <div class="form-text">A short summary of your post (optional)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required><?php 
                                echo htmlspecialchars($post['content']); 
                            ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                Publish
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="published_at" class="form-label">Publish Date & Time</label>
                                    <input type="datetime-local" class="form-control" id="published_at" name="published_at" 
                                           value="<?php echo $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : ''; ?>">
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <?php echo $is_edit ? 'Update' : 'Publish'; ?>
                                    </button>
                                    <a href="blog.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                Featured Image
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($post['featured_image'])): ?>
                                    <img src="../uploads/blog/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                         class="img-fluid mb-3" style="max-height: 200px;" alt="Featured Image">
                                <?php else: ?>
                                    <div class="bg-light p-5 mb-3 text-muted">
                                        <i class="fas fa-image fa-3x mb-2"></i>
                                        <p class="mb-0">No featured image</p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <input class="form-control" type="file" id="featured_image" name="featured_image" 
                                           accept="image/*">
                                </div>
                                
                                <?php if (!empty($post['featured_image'])): ?>
                                    <div class="form-check
                                    <input class="form-check-input" type="checkbox" id="remove_featured_image" name="remove_featured_image" value="1">
                                    <label class="form-check-label" for="remove_featured_image">
                                        Remove featured image
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            Categories
                        </div>
                        <div class="card-body">
                            <?php if (!empty($categories)): ?>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($categories as $category): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="categories[]" 
                                                   value="<?php echo $category['id']; ?>"
                                                   id="category_<?php echo $category['id']; ?>"
                                                   <?php echo in_array($category['id'], $post['categories']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="category_<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">No categories found. <a href="blog-categories.php">Add categories</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Include CKEditor -->
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
// Initialize CKEditor
CKEDITOR.replace('content', {
    toolbar: [
        { name: 'document', items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'] },
        { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
        { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] },
        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language'] },
        { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
        { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe'] },
        { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
        { name: 'colors', items: ['TextColor', 'BGColor'] },
        { name: 'tools', items: ['Maximize', 'ShowBlocks'] }
    ],
    height: 400
});

// Auto-generate slug from title
const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');

if (titleInput && slugInput) {
    titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manuallyEdited) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special chars
                .replace(/\s+/g, '-')     // Replace spaces with -
                .replace(/--+/g, '-');    // Replace multiple - with single -
            slugInput.value = slug;
        }
    });
    
    slugInput.addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });
}

// Form validation
document.getElementById('blogForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const content = CKEDITOR.instances.content.getData().trim();
    
    if (!title) {
        e.preventDefault();
        alert('Please enter a title for the post.');
        document.getElementById('title').focus();
        return false;
    }
    
    if (!content) {
        e.preventDefault();
        alert('Please enter content for the post.');
        return false;
    }
    
    return true;
});
</script>

<?php include 'includes/footer.php'; ?>
