<?php
// Start session and include config
require_once __DIR__ . '/includes/config.php';

// Initialize variables
$pdo = null;
$gallery_item = null;
$gallery_images = [];
$error = '';

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

// Check if gallery item ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error = 'Invalid gallery item ID.';
} else {
    $gallery_item_id = (int)$_GET['id'];
    
    try {
        // Get gallery item details
        $stmt = $pdo->prepare("
            SELECT 
                gi.*, 
                gc.name as category_name,
                (SELECT image_path FROM gallery_images WHERE gallery_item_id = gi.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM gallery_items gi
            LEFT JOIN gallery_categories gc ON gi.category_id = gc.id
            WHERE gi.id = ? AND gi.status = 1
        
        $stmt->execute([$gallery_item_id]);
        $gallery_item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($gallery_item) {
            // Get all images for this gallery item
            $stmt = $pdo->prepare("
                SELECT * FROM gallery_images 
                WHERE gallery_item_id = ? 
                ORDER BY is_primary DESC, display_order, id
            
            $stmt->execute([$gallery_item_id]);
            $gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no primary image is set, use the first image
            if (empty($gallery_item['primary_image']) && !empty($gallery_images)) {
                $gallery_item['primary_image'] = $gallery_images[0]['image_path'];
            }
        } else {
            $error = 'Gallery item not found or is not active.';
        }
    } catch (PDOException $e) {
        $error = 'Error fetching gallery item: ' . $e->getMessage();
        error_log($error);
    }
}

// Set page title
$page_title = $gallery_item ? htmlspecialchars($gallery_item['title']) : 'Gallery';

// Include header
include 'includes/header.php';
?>

<!-- Page Header -->
<header class="page-header bg-light py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 mb-3"><?php echo $gallery_item ? htmlspecialchars($gallery_item['title']) : 'Gallery'; ?></h1>
                <?php if (!empty($gallery_item['category_name'])): ?>
                    <p class="lead text-muted">
                        <a href="gallery.php?category=<?php echo $gallery_item['category_id']; ?>" class="text-decoration-none">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($gallery_item['category_name']); ?>
                        </a>
                    </p>
                <?php endif; ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="gallery.php">Gallery</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo $gallery_item ? htmlspecialchars($gallery_item['title']) : 'View'; ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
                <p class="mt-3 mb-0">
                    <a href="gallery.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Gallery
                    </a>
                </p>
            </div>
        <?php elseif (empty($gallery_images)): ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-image fa-5x text-muted"></i>
                </div>
                <h3 class="h4 mb-3">No Images Found</h3>
                <p class="text-muted mb-4">This gallery item doesn't have any images yet.</p>
                <a href="gallery.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Gallery
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <!-- Main Image Slider -->
                    <div class="gallery-main mb-4">
                        <div class="glide__track" data-glide-el="track">
                            <ul class="glide__slides">
                                <?php foreach ($gallery_images as $index => $image): ?>
                                    <li class="glide__slide">
                                        <div class="ratio ratio-16x9">
                                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                 class="img-fluid rounded shadow" 
                                                 alt="<?php echo htmlspecialchars($gallery_item['title'] . ' - Image ' . ($index + 1)); ?>">
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <div class="glide__arrows" data-glide-el="controls">
                                <button class="glide__arrow glide__arrow--left" data-glide-dir="<">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="glide__arrow glide__arrow--right" data-glide-dir=">">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            
                            <div class="glide__bullets" data-glide-el="controls[nav]">
                                <?php foreach ($gallery_images as $index => $image): ?>
                                    <button class="glide__bullet" data-glide-dir="=<?php echo $index; ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thumbnail Navigation -->
                    <?php if (count($gallery_images) > 1): ?>
                        <div class="gallery-thumbs mt-3">
                            <div class="glide__track" data-glide-el="controls[nav]">
                                <div class="glide__slides">
                                    <?php foreach ($gallery_images as $index => $image): ?>
                                        <div class="glide__slide">
                                            <div class="ratio ratio-4x3">
                                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                     class="img-fluid rounded cursor-pointer"
                                                     style="object-fit: cover;"
                                                     data-index="<?php echo $index; ?>"
                                                     alt="Thumbnail <?php echo $index + 1; ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h4 mb-3"><?php echo htmlspecialchars($gallery_item['title']); ?></h2>
                            
                            <?php if (!empty($gallery_item['description'])): ?>
                                <div class="mb-4">
                                    <h3 class="h5 mb-2">Description</h3>
                                    <div class="text-muted">
                                        <?php echo nl2br(htmlspecialchars($gallery_item['description'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <div class="badge bg-light text-dark p-2">
                                    <i class="fas fa-images me-1"></i>
                                    <?php echo count($gallery_images); ?> image<?php echo count($gallery_images) !== 1 ? 's' : ''; ?>
                                </div>
                                
                                <?php if (!empty($gallery_item['category_name'])): ?>
                                    <a href="gallery.php?category=<?php echo $gallery_item['category_id']; ?>" class="badge bg-primary text-white text-decoration-none p-2">
                                        <i class="fas fa-tag me-1"></i>
                                        <?php echo htmlspecialchars($gallery_item['category_name']); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($gallery_item['is_featured']): ?>
                                    <span class="badge bg-warning text-dark p-2">
                                        <i class="fas fa-star me-1"></i> Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="gallery.php" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Gallery
                                </a>
                                
                                <?php if (!empty($gallery_item['primary_image'])): ?>
                                    <a href="<?php echo htmlspecialchars($gallery_item['primary_image']); ?>" 
                                       class="btn btn-outline-secondary" 
                                       download="<?php echo htmlspecialchars(preg_replace('/[^a-z0-9]+/', '-', strtolower($gallery_item['title'])) . '-image.jpg'); ?>">
                                        <i class="fas fa-download me-2"></i> Download Image
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Items -->
            <?php
            try {
                $related_query = "
                    SELECT 
                        gi.id, 
                        gi.title,
                        (SELECT image_path FROM gallery_images WHERE gallery_item_id = gi.id AND is_primary = 1 LIMIT 1) as primary_image
                    FROM gallery_items gi
                    WHERE gi.status = 1 
                    AND gi.id != ?
                
                // If category is set, filter by category
                if (!empty($gallery_item['category_id'])) {
                    $related_query .= " AND gi.category_id = ?";
                    $related_params = [$gallery_item_id, $gallery_item['category_id']];
                } else {
                    $related_params = [$gallery_item_id];
                }
                
                $related_query .= " ORDER BY RAND() LIMIT 3";
                
                $stmt = $pdo->prepare($related_query);
                $stmt->execute($related_params);
                $related_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($related_items)):
            ?>
                <div class="mt-5 pt-5 border-top">
                    <h3 class="h4 mb-4">You May Also Like</h3>
                    <div class="row g-4">
                        <?php foreach ($related_items as $related): ?>
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <?php if (!empty($related['primary_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($related['primary_image']); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($related['title']); ?>"
                                             style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title h6 mb-0">
                                            <a href="gallery-view.php?id=<?php echo $related['id']; ?>" class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($related['title']); ?>
                                            </a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
                endif;
            } catch (PDOException $e) {
                // Silently fail related items
                error_log("Error fetching related items: " . $e->getMessage());
            }
            ?>
        <?php endif; ?>
    </div>
</main>

<?php
// Include footer
include 'includes/footer.php';
?>

<!-- Glide.js for image gallery -->
<script src="https://cdn.jsdelivr.net/npm/@glidejs/glide"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize main gallery slider
    const mainGlide = new Glide('.gallery-main', {
        type: 'carousel',
        startAt: 0,
        perView: 1,
        focusAt: 'center',
        gap: 0,
        autoplay: 5000,
        hoverpause: true,
        keyboard: true
    });
    
    // Initialize thumbnail gallery if it exists
    const thumbs = document.querySelector('.gallery-thumbs');
    if (thumbs) {
        const thumbGlide = new Glide('.gallery-thumbs', {
            type: 'slider',
            startAt: 0,
            perView: 4,
            focusAt: 'center',
            gap: 10,
            keyboard: true,
            bound: true,
            breakpoints: {
                992: {
                    perView: 3
                },
                768: {
                    perView: 4
                },
                576: {
                    perView: 3
                }
            }
        });
        
        // Sync main slider with thumbnail slider
        mainGlide.on(['mount.after', 'run.after'], function() {
            thumbGlide.go('=' + mainGlide.index);
        });
        
        thumbGlide.on('click', function() {
            mainGlide.go('=' + thumbGlide.index);
        });
        
        // Handle thumbnail clicks
        document.querySelectorAll('.gallery-thumbs .glide__slide').forEach((slide, index) => {
            slide.addEventListener('click', () => {
                mainGlide.go('=' + index);
            });
        });
        
        thumbGlide.mount();
    }
    
    mainGlide.mount();
    
    // Add zoom functionality
    const mainSlide = document.querySelector('.gallery-main .glide__slide');
    if (mainSlide) {
        const img = mainSlide.querySelector('img');
        let isZoomed = false;
        
        img.addEventListener('click', function() {
            if (isZoomed) {
                this.style.transform = 'scale(1)';
                this.style.cursor = 'zoom-in';
            } else {
                this.style.transform = 'scale(1.5)';
                this.style.cursor = 'zoom-out';
            }
            isZoomed = !isZoomed;
        });
    }
});
</script>

<style>
/* Gallery styles */
.gallery-main {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.gallery-thumbs {
    margin: 0 -0.25rem;
}

.gallery-thumbs .glide__slide {
    opacity: 0.6;
    transition: opacity 0.2s ease;
    cursor: pointer;
}

.gallery-thumbs .glide__slide.glide__slide--active {
    opacity: 1;
    border: 2px solid var(--bs-primary);
    border-radius: 0.25rem;
}

.gallery-thumbs img {
    transition: transform 0.2s ease;
}

.gallery-thumbs .glide__slide:hover img {
    transform: scale(1.05);
}

/* Glide arrows */
.glide__arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    background: rgba(255, 255, 255, 0.8);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #333;
    font-size: 1rem;
    line-height: 1;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    opacity: 0;
}

.glide__arrow--left {
    left: 1rem;
}

.glide__arrow--right {
    right: 1rem;
}

.gallery-main:hover .glide__arrow {
    opacity: 1;
}

.glide__arrow:hover {
    background: #fff;
    color: var(--bs-primary);
}

/* Bullets */
.glide__bullets {
    position: absolute;
    bottom: 1.5rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 0.5rem;
    z-index: 2;
}

.glide__bullet {
    width: 10px;
    height: 10px;
    padding: 0;
    border: none;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    transition: all 0.2s ease;
    cursor: pointer;
}

.glide__bullet--active,
.glide__bullet:hover {
    background: #fff;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .gallery-main {
        border-radius: 0;
    }
    
    .gallery-thumbs {
        display: none;
    }
    
    .glide__arrow {
        opacity: 1 !important;
        width: 30px;
        height: 30px;
    }
    
    .glide__arrow--left {
        left: 0.5rem;
    }
    
    .glide__arrow--right {
        right: 0.5rem;
    }
}
</style>
