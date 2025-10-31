<?php
// Set page title
$page_title = 'Home';

// Include config file which contains database connection
require_once 'includes/config.php';

// Get database connection
$pdo = getDBConnection();
if ($pdo === null) {
    die("Error: Could not connect to the database.");
}

// Include header
include 'includes/header.php';

// Initialize services array
$featured_services = [];

// Get featured services from database if connection is successful
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM services WHERE is_featured = 1 LIMIT 3");
        $featured_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching featured services: " . $e->getMessage());
        // Continue with empty services array
    }
}
?>
<section class="work-hero-section py-5">
    <div class="container-fluid px-0">
        <div class="row justify-content-center mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-2">Welcome to <span class="text-primary">Nirali Makeup Studio</span></h2>
                <p class="lead mb-4">Transform your look with our professional makeup artistry services. Specializing in bridal, party, and special occasion makeup.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="booking.php" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-calendar-check me-2"></i>Book Appointment
                    </a>
                    <a href="services.php" class="btn btn-outline-primary btn-lg px-4">
                        <i class="fas fa-spa me-2"></i>View Services
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-12">
                <div id="workSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="work-image-container" style="width: 1920px; height: 1080px; margin: 0 auto; overflow: hidden;">
                                <img src="assets/images/hero-1.jpg" alt="Makeup Work 1" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        
                        <div class="carousel-item">
                            <div class="work-image-container" style="width: 1920px; height: 1080px; margin: 0 auto; overflow: hidden;">
                                <img src="assets/images/hero-3.jpg" alt="Makeup Work 3" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#workSlider" data-bs-slide="prev" style="left: calc(50% - 960px); width: 50px; height: 100%;">
                        <span class="carousel-control-prev-icon bg-dark bg-opacity-50 rounded-end" aria-hidden="true" style="padding: 1.5rem;"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#workSlider" data-bs-slide="next" style="right: calc(50% - 960px); width: 50px; height: 100%;">
                        <span class="carousel-control-next-icon bg-dark bg-opacity-50 rounded-start" aria-hidden="true" style="padding: 1.5rem;"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.work-hero-section {
    overflow: hidden;
}

.work-hero-section .carousel-control-prev,
.work-hero-section .carousel-control-next {
    opacity: 0.7;
    transition: opacity 0.3s ease;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 100px;
    margin-top: 0;
}

.work-hero-section .carousel-control-prev {
    left: 0;
    justify-content: flex-start;
    padding-left: 1rem;
}

.work-hero-section .carousel-control-next {
    right: 0;
    justify-content: flex-end;
    padding-right: 1rem;
}

.work-hero-section .carousel-control-prev-icon,
.work-hero-section .carousel-control-next-icon {
    background-size: 1.5rem;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.work-hero-section .carousel-control-prev:hover,
.work-hero-section .carousel-control-next:hover {
    opacity: 1;
}

@media (max-width: 1920px) {
    .work-hero-section .work-image-container {
        width: 100% !important;
        max-width: 1920px;
        height: auto !important;
        max-height: 1080px;
        aspect-ratio: 16/9;
    }
}

@media (max-width: 768px) {
    .work-hero-section .carousel-control-prev,
    .work-hero-section .carousel-control-next {
        width: 30px;
        height: 60px;
    }
    
    .work-hero-section .carousel-control-prev-icon,
    .work-hero-section .carousel-control-next-icon {
        width: 2rem;
        height: 2rem;
        background-size: 1rem;
    }
}

</style>
<!-- Our Work Section -->
<section class="work-section py-5">
    <div class="container-fluid px-0">
        <div class="row justify-content-center mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-2">Our <span class="text-primary">Work</span></h2>
                <p class="lead">Explore our latest makeup and beauty transformations</p>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-12">
                <div id="workSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="work-image-container" style="width: 1920px; height: 1080px; margin: 0 auto; overflow: hidden;">
                                <img src="assets/images/1.png" alt="Makeup Work 1" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="work-image-container" style="width: 1920px; height: 1080px; margin: 0 auto; overflow: hidden;">
                                <img src="assets/images/2.png" alt="Makeup Work 2" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="work-image-container" style="width: 1920px; height: 1080px; margin: 0 auto; overflow: hidden;">
                                <img src="assets/images/3.png" alt="Makeup Work 3" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="work-image-container" style="width: 1920px; height: 1080px; margin: 0 auto; overflow: hidden;">
                                <img src="assets/images/4.png" alt="Makeup Work 4" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#workSlider" data-bs-slide="prev" style="left: calc(50% - 960px); width: 50px; height: 100%;">
                        <span class="carousel-control-prev-icon bg-dark bg-opacity-50 rounded-end" aria-hidden="true" style="padding: 1.5rem;"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#workSlider" data-bs-slide="next" style="right: calc(50% - 960px); width: 50px; height: 100%;">
                        <span class="carousel-control-next-icon bg-dark bg-opacity-50 rounded-start" aria-hidden="true" style="padding: 1.5rem;"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.work-section {
    overflow: hidden;
}

.work-section .carousel-control-prev,
.work-section .carousel-control-next {
    opacity: 0.7;
    transition: opacity 0.3s ease;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 100px;
    margin-top: 0;
}

.work-section .carousel-control-prev {
    left: 0;
    justify-content: flex-start;
    padding-left: 1rem;
}

.work-section .carousel-control-next {
    right: 0;
    justify-content: flex-end;
    padding-right: 1rem;
}

.work-section .carousel-control-prev-icon,
.work-section .carousel-control-next-icon {
    background-size: 1.5rem;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.work-section .carousel-control-prev:hover,
.work-section .carousel-control-next:hover {
    opacity: 1;
}

@media (max-width: 1920px) {
    .work-section .work-image-container {
        width: 100% !important;
        max-width: 1920px;
        height: auto !important;
        max-height: 1080px;
        aspect-ratio: 16/9;
    }
}

@media (max-width: 768px) {
    .work-section .carousel-control-prev,
    .work-section .carousel-control-next {
        width: 30px;
        height: 60px;
    }
    
    .work-section .carousel-control-prev-icon,
    .work-section .carousel-control-next-icon {
        width: 2rem;
        height: 2rem;
        background-size: 1rem;
    }
}

.hero-section {
        margin-top: -50px;
        padding-top: 30px;
    }
    .hero-section h1,
    .hero-section p,
    .hero-section .btn {
        color: white !important;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    }
</style>

<!-- Custom CSS for services -->
<link href="assets/css/custom.css" rel="stylesheet">

<!-- Featured Services -->
<section id="services" class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Our <span class="text-primary"></span> Services</h2>
                <p class="lead">Discover our most popular beauty treatments and services</p>
            </div>
        </div>
        
        <div class="row">
            <?php 
            // Get 4 featured services
            try {
                $stmt = $pdo->query("SELECT * FROM services WHERE is_featured = 1 AND status = 1 ORDER BY display_order LIMIT 4");
                $featured_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // If less than 4 featured services, get random services to fill
                if (count($featured_services) < 4) {
                    $needed = 4 - count($featured_services);
                    $exclude_ids = !empty($featured_services) ? array_column($featured_services, 'id') : [0];
                    $placeholders = rtrim(str_repeat('?,', count($exclude_ids)), ',');
                    
                    $stmt = $pdo->prepare("
                        SELECT * FROM services 
                        WHERE status = 1 
                        AND id NOT IN ($placeholders)
                        ORDER BY RAND() 
                        LIMIT ?
                    ");
                    
                    $params = array_merge($exclude_ids, [$needed]);
                    $stmt->execute($params);
                    $additional_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $featured_services = array_merge($featured_services, $additional_services);
                }
            } catch (PDOException $e) {
                error_log("Error fetching services: " . $e->getMessage());
                $featured_services = [];
            }
            
            if (!empty($featured_services)): 
                foreach ($featured_services as $service): 
                    $service_title = htmlspecialchars($service['title'] ?? 'Service');
                    $service_desc = !empty($service['short_description']) ? 
                        htmlspecialchars($service['short_description']) : 
                        (strlen($service['description'] ?? '') > 100 ? 
                            substr(strip_tags($service['description']), 0, 100) . '...' : 
                            strip_tags($service['description'] ?? ''));
                    $service_image = !empty($service['image']) ? 
                        htmlspecialchars($service['image']) : 
                        'assets/images/service-placeholder.jpg';
                    $service_duration = !empty($service['duration']) ? $service['duration'] . ' mins' : 'Duration varies';
                   
                    $service_slug = $service['slug'] ?? '';
            ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 service-card">
                        <div class="service-card-img">
                            <img src="<?php echo $service_image; ?>" class="card-img-top" alt="<?php echo $service_title; ?>">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo $service_title; ?></h5>
                            <p class="card-text flex-grow-1"><?php echo $service_desc; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    <i class="far fa-clock me-1"></i> 
                                    <?php echo $service_duration; ?>
                                </span>
                                <?php if (!empty($service_price)): ?>
                                    <span class="text-primary fw-bold"><?php echo $service_price; ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="service-details.php?slug=<?php echo $service_slug; ?>" class="btn btn-outline-primary mt-3">
                                View Details <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach; 
            else: 
                echo '<div class="col-12">
                    <div class="alert alert-info">
                        No services available at the moment. Please check back later.
                    </div>
                </div>';
            endif; 
            ?>
        </div>
        
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="services.php" class="btn btn-primary px-4">
                View All Services <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Add this to your existing custom.css or in a style tag in head -->
<style>
/* Pinterest Style Masonry Layout */
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    padding: 1rem;
}

.service-card {
    break-inside: avoid;
    margin-bottom: 1.5rem;
    border-radius: 12px;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.service-card-img {
    width: 100%;
    padding-top: 100%; /* 1:1 Aspect Ratio */
    position: relative;
    overflow: hidden;
}

.service-card-img img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.service-card:hover .service-card-img img {
    transform: scale(1.1);
}

.card-body {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
}

.card-title {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: #333;
}

.card-text {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    flex-grow: 1;
}

.service-meta {
    font-size: 0.85rem;
    color: #888;
    margin-bottom: 1rem;
}

.btn-outline-primary {
    border-width: 2px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        padding: 0.5rem;
    }
    
    .service-card {
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    .services-grid {
        grid-template-columns: 1fr;
        padding: 0.5rem;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize hero image slider
    var heroImageSlider = document.getElementById('heroImageSlider');
    if (heroImageSlider) {
        var carousel = new bootstrap.Carousel(heroImageSlider, {
            interval: 3000,
            ride: 'carousel',
            wrap: true,
            touch: true
        });
        
        // Pause on hover
        heroImageSlider.addEventListener('mouseenter', function() {
            carousel.pause();
        });
        
        heroImageSlider.addEventListener('mouseleave', function() {
            carousel.cycle();
        });
    }
});
</script>
<?php


// Include videos section
include 'includes/videos-section.php';

// Include testimonials section
include 'includes/testimonials.php';


// Include footer
include 'includes/footer.php';
?>