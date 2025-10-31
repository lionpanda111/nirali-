<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/../includes/config.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Blog Categories';
$active_menu = 'blog';

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        // Add new category
        $name = trim($_POST['name'] ?? '');
        $slug = createSlug($name);
        $description = trim($_POST['description'] ?? '');
        
        if (!empty($name)) {
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (?, ?, ?)");
                $stmt->execute([$name, $slug, $description]);
                
                $_SESSION['success_message'] = 'Category added successfully!';
                header('Location: blog-categories.php');
                exit();
                
            } catch (PDOException $e) {
                $error = 'Error adding category: ' . $e->getMessage();
            }
        } else {
            $error = 'Category name is required.';
        }
    } 
    elseif (isset($_POST['update_category'])) {
        // Update existing category
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = createSlug($name);
        $description = trim($_POST['description'] ?? '');
        
        if ($id > 0 && !empty($name)) {
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("UPDATE blog_categories SET name = ?, slug = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $id]);
                
                $_SESSION['success_message'] = 'Category updated successfully!';
                header('Location: blog-categories.php');
                exit();
                
            } catch (PDOException $e) {
                $error = 'Error updating category: ' . $e->getMessage();
            }
        } else {
            $error = 'Invalid category data.';
        }
    }
}

// Handle category deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    
    try {
        $db = getDBConnection();
        
        // Check if category is in use
        $stmt = $db->prepare("SELECT COUNT(*) FROM blog_post_categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $in_use = $stmt->fetchColumn() > 0;
        
        if ($in_use) {
            $_SESSION['error_message'] = 'Cannot delete category because it is in use by one or more blog posts.';
        } else {
            // Delete category
            $stmt = $db->prepare("DELETE FROM blog_categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $_SESSION['success_message'] = 'Category deleted successfully!';
        }
        
        header('Location: blog-categories.php');
        exit();
        
    } catch (PDOException $e) {
        $error = 'Error deleting category: ' . $e->getMessage();
    }
}

// Get all categories
$db = getDBConnection();
$categories = $db->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

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
        <li class="breadcrumb-item"><a href="blog.php">Blog</a></li>
        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
    </ol>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Add/Edit Category Form -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus me-1"></i>
                    <?php echo isset($_GET['edit']) ? 'Edit' : 'Add New'; ?> Category
                </div>
                <div class="card-body">
                    <?php
                    $category = ['id' => '', 'name' => '', 'description' => ''];
                    $form_action = 'blog-categories.php';
                    
                    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
                        $edit_id = (int)$_GET['edit'];
                        $stmt = $db->prepare("SELECT * FROM blog_categories WHERE id = ?");
                        $stmt->execute([$edit_id]);
                        $category = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$category) {
                            $_SESSION['error_message'] = 'Category not found.';
                            header('Location: blog-categories.php');
                            exit();
                        }
                    }
                    ?>
                    
                    <form method="post" action="<?php echo $form_action; ?>">
                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($category['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo htmlspecialchars($category['description']); 
                            ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <?php if (isset($_GET['edit'])): ?>
                                <button type="submit" name="update_category" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Category
                                </button>
                                <a href="blog-categories.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            <?php else: ?>
                                <button type="submit" name="add_category" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add Category
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Categories List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    All Categories
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <div class="alert alert-info mb-0">
                            No categories found. Add your first category using the form on the left.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Posts</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                <?php if ($category['description']): ?>
                                                    <div class="text-muted small">
                                                        <?php echo htmlspecialchars($category['description']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                            <td>
                                                <?php 
                                                $stmt = $db->prepare("SELECT COUNT(*) FROM blog_post_categories WHERE category_id = ?");
                                                $stmt->execute([$category['id']]);
                                                echo $stmt->fetchColumn();
                                                ?>
                                            </td>
                                            <td>
                                                <a href="blog-categories.php?edit=<?php echo $category['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="#" 
                                                   onclick="return confirmDelete(<?php echo $category['id']; ?>)" 
                                                   class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
        window.location.href = 'blog-categories.php?delete=' + id;
    }
    return false;
}
</script>

<?php include 'includes/footer.php'; ?>
