<?php
/**
 * Service Card Component
 * 
 * @param array $service Service data array containing id, title, description, image, etc.
 * @param string $class Additional CSS classes (optional)
 */
function render_service_card($service, $class = '') {
    $image = !empty($service['image']) ? $service['image'] : 'assets/images/default-service.jpg';
    $title = htmlspecialchars($service['title']);
    $description = !empty($service['short_description']) ? 
        htmlspecialchars($service['short_description']) : 
        (strlen($service['description']) > 100 ? 
            substr(strip_tags($service['description']), 0, 100) . '...' : 
            strip_tags($service['description']));
    $duration = $service['duration'] ?? 60;
    $slug = $service['slug'] ?? '';
    
    ?>
    <div class="col-lg-4 col-md-6 mb-4 <?php echo $class; ?>" data-aos="fade-up">
        <div class="card service-card h-100 d-flex flex-column border-0 shadow-sm">
            <div class="position-relative flex-shrink-0">
                <img src="<?php echo $image; ?>" class="card-img-top" alt="<?php echo $title; ?>" style="height: 200px; object-fit: cover;">
                <?php if (!empty($service['is_featured'])): ?>
                    <div class="position-absolute top-0 end-0 bg-warning text-white px-3 py-1 m-2 rounded-pill small">
                        <i class="fas fa-star me-1"></i> Featured
                    </div>
                <?php endif; ?>
                <div class="card-img-overlay d-flex align-items-end p-0">
                    <div class="price-tag bg-primary text-white px-3 py-2">
                        <small class="d-block"><?php echo $duration; ?> mins</small>
                    </div>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-2"><?php echo $title; ?></h5>
                <p class="card-text text-muted small flex-grow-1"><?php echo $description; ?></p>
                <div class="mt-3 pt-2 border-top">
                    <a href="service-details.php?slug=<?php echo $slug; ?>" class="btn btn-primary w-100">
                        View Details <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
