<?php
// Initialize gallery images array
$gallery_images = [];

// Get gallery images from database if connection is available
if (isset($pdo) && $pdo) {
    try {
        $stmt = $pdo->query("SELECT gi.*, gc.name as category_name 
                             FROM gallery_images gi 
                             LEFT JOIN gallery_categories gc ON gi.category_id = gc.id 
                             WHERE gi.is_featured = 1 
                             ORDER BY gi.created_at DESC 
                             LIMIT 6");
        $gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching gallery images: " . $e->getMessage());
        // Continue with empty gallery images array
    }
}
?>

<!-- Gallery Section -->
<section id="gallery" class="py-5" style="background-color: white;">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2>Our Work</h2>
            <p class="text-muted">Explore our latest makeup and beauty transformations</p>
        </div>

        <?php if (!empty($gallery_images)): ?>
            <div class="row g-4">
                <?php foreach ($gallery_images as $image): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="gallery-item position-relative overflow-hidden rounded" style="border-radius: 10px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';">
                            <div class="portrait-ratio">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                     class="img-fluid w-100 h-100" 
                                     alt="<?php echo htmlspecialchars($image['title']); ?>"
                                     loading="lazy"
                                     style="object-fit: cover; object-position: center top;">
                            </div>
                            <style>
                                .portrait-ratio {
                                    position: relative;
                                    padding-bottom: 150%; /* Creates a 2:3 aspect ratio (portrait) */
                                    height: 0;
                                    overflow: hidden;
                                }
                                .portrait-ratio img {
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                }
                            </style>
                            <a href="<?php echo htmlspecialchars($image['image_path']); ?>" 
                               class="gallery-preview d-block" 
                               data-fslightbox="gallery"
                               data-caption="<h4><?php echo htmlspecialchars($image['title']); ?></h4><?php if (!empty($image['description'])) echo '<p>' . htmlspecialchars($image['description']) . '</p>'; ?>">
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="gallery.php" class="btn" style="background-color: #FFD700; color: #333; border: none; font-weight: 600; padding: 0.75rem 2rem; border-radius: 50px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">View Full Gallery</a>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <p class="text-muted">No gallery images available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Add FS Lightbox for image previews -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fslightbox@3.3.0/index.min.css">
<script src="https://cdn.jsdelivr.net/npm/fslightbox@3.3.0/index.min.js"></script>
