<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Initialize variables
$gallery_images = [];
$categories = [];
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get database connection
try {
    $pdo = getDBConnection();
    
    // Fetch all active categories
    $category_query = "SELECT id, name FROM gallery_categories WHERE status = 1 ORDER BY display_order, name";
    $stmt = $pdo->query($category_query);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch gallery images with optional category filter
    $params = [];
    $where_conditions = ["gi.status = 1"];
    
    if ($selected_category) {
        $where_conditions[] = "gi.category_id = ?";
        $params[] = $selected_category;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    $query = "SELECT gi.*, gc.name as category_name 
              FROM gallery_images gi 
              LEFT JOIN gallery_categories gc ON gi.category_id = gc.id 
              $where_clause
              ORDER BY gi.display_order, gi.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Error loading gallery. Please try again later.";
}
?>

<!-- Page Header -->
<section class="py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="h4 mb-0">Gallery</h1>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="pb-4">
    <div class="container">
        <!-- Category Filter -->
        <div class="mb-3">
            <div class="d-flex flex-wrap gap-2">
                <a href="gallery.php" class="btn btn-sm btn-outline-secondary <?php echo !$selected_category ? 'active' : ''; ?>">
                    All
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="gallery.php?category=<?php echo $category['id']; ?>" 
                       class="btn btn-sm btn-outline-secondary <?php echo $selected_category == $category['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gallery Grid -->
        <?php if (!empty($gallery_images)): ?>
            <style>
                .portrait-ratio {
                    position: relative;
                    padding-bottom: 150%; /* Creates a 2:3 aspect ratio (portrait) */
                    height: 0;
                    overflow: hidden;
                    border-radius: 10px;
                    transition: transform 0.3s ease;
                }
                .portrait-ratio:hover {
                    transform: translateY(-5px);
                }
                .portrait-ratio img {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    object-position: center top;
                    transition: transform 0.3s ease;
                }
                .portrait-ratio:hover img {
                    transform: scale(1.03);
                }
                .gallery-caption {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: linear-gradient(transparent, rgba(0,0,0,0.7));
                    color: white;
                    padding: 1rem;
                    transform: translateY(100%);
                    transition: transform 0.3s ease;
                }
                .portrait-ratio:hover .gallery-caption {
                    transform: translateY(0);
                }
            </style>
            <div class="row g-4">
                <?php foreach ($gallery_images as $image): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="gallery-item position-relative">
                            <div class="portrait-ratio">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($image['title']); ?>"
                                     loading="lazy">
                                <div class="gallery-caption">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($image['title']); ?></h6>
                                    <?php if (!empty($image['category_name'])): ?>
                                        <small class="text-white-50"><?php echo htmlspecialchars($image['category_name']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                   class="gallery-preview d-block position-absolute top-0 start-0 w-100 h-100" 
                                   data-fslightbox="gallery"
                                   data-caption="<h5><?php echo htmlspecialchars($image['title']); ?></h5><?php if (!empty($image['description'])) echo '<p>' . htmlspecialchars($image['description']) . '</p>'; ?>">
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="alert alert-info">
                    <i class="fas fa-images fa-3x mb-3"></i>
                    <h4>No images found</h4>
                    <p class="mb-0">Check back later for updates to our gallery.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Add FS Lightbox for image previews -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fslightbox@3.3.0/index.min.css">
<script src="https://cdn.jsdelivr.net/npm/fslightbox@3.3.0/index.min.js"></script>

<!-- Add hover effect -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.querySelector('.gallery-overlay').style.opacity = '1';
        });
        
        item.addEventListener('mouseleave', function() {
            this.querySelector('.gallery-overlay').style.opacity = '0';
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
