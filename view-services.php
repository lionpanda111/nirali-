<?php
// Include the database configuration
require_once 'includes/config.php';

// Set page title
$page_title = 'View Services';

// Include header
include 'includes/header.php';

try {
    // Get database connection
    $pdo = getDBConnection();
    
    // Fetch all active services, ordered by display_order
    $stmt = $pdo->query("SELECT * FROM services WHERE status = 1 ORDER BY display_order, title");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch featured services for the grid
    $featuredStmt = $pdo->query("SELECT * FROM services WHERE is_featured = 1 AND status = 1 ORDER BY display_order LIMIT 4");
    $featured_services = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Error: Could not retrieve services. Please try again later.");
}
?>

<!-- Services Grid Section -->
<section id="services" class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Our <span class="text-primary">Featured</span> Services</h2>
                <p class="lead">Discover our most popular beauty treatments and services</p>
            </div>
        </div>
        
        <?php if (!empty($featured_services)): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($featured_services as $service): 
                    $service_title = htmlspecialchars($service['title']);
                    $service_desc = !empty($service['short_description']) ? 
                        htmlspecialchars($service['short_description']) : 
                        (strlen($service['description'] ?? '') > 100 ? 
                            substr(strip_tags($service['description']), 0, 100) . '...' : 
                            strip_tags($service['description'] ?? ''));
                    $service_image = !empty($service['image']) ? 
                        htmlspecialchars($service['image']) : 
                        'assets/images/service-placeholder.jpg';
                    $service_duration = !empty($service['duration']) ? $service['duration'] . ' mins' : 'Duration varies';
                    $service_price = !empty($service['price']) ? '₹' . number_format($service['price'], 2) : '';
                ?>
                    <div class="col" data-aos="fade-up">
                        <div class="card h-100 border-0 shadow-sm overflow-hidden">
                            <div class="service-card-img position-relative" style="height: 200px; overflow: hidden;">
                                <img src="<?php echo $service_image; ?>" 
                                     class="card-img-top h-100 w-100" 
                                     alt="<?php echo $service_title; ?>"
                                     style="object-fit: cover;">
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo $service_title; ?></h5>
                                    <?php if (!empty($service_price)): ?>
                                        <span class="badge bg-primary rounded-pill px-3 py-2">
                                            <?php echo $service_price; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="service-meta mb-3">
                                    <span class="text-muted small">
                                        <i class="far fa-clock me-1"></i> 
                                        <?php echo $service_duration; ?>
                                    </span>
                                </div>
                                
                                <p class="card-text text-muted small">
                                    <?php echo $service_desc; ?>
                                </p>
                                
                                <div class="text-center mt-3">
                                    <a href="service-details.php?slug=<?php echo urlencode($service['slug']); ?>" 
                                       class="btn btn-outline-primary w-100">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No featured services found. Please add some services from the admin panel.
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- All Services Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">All <span class="text-primary">Services</span></h2>
                <p class="lead">Explore our complete range of beauty services</p>
            </div>
        </div>
        
        <?php if (!empty($services)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($services as $service): 
                    $service_title = htmlspecialchars($service['title']);
                    $service_desc = !empty($service['short_description']) ? 
                        htmlspecialchars($service['short_description']) : 
                        (strlen($service['description'] ?? '') > 100 ? 
                            substr(strip_tags($service['description']), 0, 100) . '...' : 
                            strip_tags($service['description'] ?? ''));
                    $service_image = !empty($service['image']) ? 
                        htmlspecialchars($service['image']) : 
                        'assets/images/service-placeholder.jpg';
                    $service_duration = !empty($service['duration']) ? $service['duration'] . ' mins' : 'Duration varies';
                    $service_price = !empty($service['price']) ? '₹' . number_format($service['price'], 2) : '';
                ?>
                    <div class="col" data-aos="fade-up">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="position-relative" style="height: 200px; overflow: hidden;">
                                <img src="<?php echo $service_image; ?>" 
                                     class="card-img-top h-100 w-100" 
                                     alt="<?php echo $service_title; ?>"
                                     style="object-fit: cover;">
                                <?php if ($service['is_featured']): ?>
                                    <span class="position-absolute top-0 end-0 m-2 bg-warning text-dark px-2 py-1 small rounded">
                                        Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $service_title; ?></h5>
                                <p class="card-text text-muted small">
                                    <?php echo $service_desc; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">
                                        <i class="far fa-clock me-1"></i> <?php echo $service_duration; ?>
                                    </span>
                                    <span class="fw-bold text-primary"><?php echo $service_price; ?></span>
                                </div>
                                <div class="mt-3">
                                    <a href="service-details.php?slug=<?php echo urlencode($service['slug']); ?>" 
                                       class="btn btn-outline-primary w-100">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No services found. Please add some services from the admin panel.
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
