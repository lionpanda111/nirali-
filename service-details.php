<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get database connection
$pdo = getDBConnection();
if ($pdo === null) {
    die("Error: Could not connect to the database.");
}

// Check if slug is provided
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: services.php');
    exit();
}

$slug = trim($_GET['slug']);

// Debug: Log the requested slug
error_log("Trying to load service with slug: " . $slug);

// Get service details from database
try {
    // First, check if the services table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'services'")->rowCount() > 0;
    
    if (!$tableExists) {
        throw new Exception("The 'services' table does not exist in the database.");
    }
    
    // Get service details with categories
    $query = "SELECT s.*, 
                     GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names,
                     GROUP_CONCAT(DISTINCT c.slug SEPARATOR ',') as category_slugs
              FROM services s 
              LEFT JOIN service_category_mapping m ON s.id = m.service_id
              LEFT JOIN service_categories c ON m.category_id = c.id 
              WHERE s.slug = ? AND s.status = 1";
              
    error_log("Executing query: " . $query);
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$slug]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Query result: " . print_r($service, true));
    
    if (!$service) {
        // Log the error
        error_log("Service not found with slug: " . $slug);
        
        // Show a more helpful error message
        $allServices = $pdo->query("SELECT slug, title FROM services WHERE status = 1 LIMIT 5")->fetchAll();
        $available = array_map(function($s) {
            return "<a href='service-details.php?slug=" . urlencode($s['slug']) . "'>" . 
                   htmlspecialchars($s['title']) . "</a>";
        }, $allServices);
        
        die("<div style='padding:20px;font-family:Arial;max-width:800px;margin:0 auto;'>
            <h2>Service Not Found</h2>
            <p>The service you're looking for doesn't exist or is no longer available.</p>
            <p>Available services: " . implode(", ", $available) . "</p>
            <p><a href='services.php'>View All Services</a></p>
        </div>");
    }
    
    // Set page title and meta description
    $page_title = $service['title'] . ' - ' . SITE_NAME;
    $meta_description = $service['meta_description'] ?? substr(strip_tags($service['description']), 0, 160);
    $meta_keywords = $service['meta_keywords'] ?? '';
    
    // Get related services (from the same categories)
    $relatedQuery = "SELECT DISTINCT s.* 
                    FROM services s
                    JOIN service_category_mapping m ON s.id = m.service_id
                    WHERE m.category_id IN (
                        SELECT category_id 
                        FROM service_category_mapping 
                        WHERE service_id = :main_service_id
                    )
                    AND s.id != :service_id
                    AND s.status = 1
                    ORDER BY RAND()
                    LIMIT 3";
    $relatedStmt = $pdo->prepare($relatedQuery);
    $relatedStmt->execute([
        'main_service_id' => $service['id'],
        'service_id' => $service['id']
    ]);
    $relatedServices = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Log detailed error
    $errorMsg = 'Error in service-details.php: ' . $e->getMessage() . "\n";
    $errorMsg .= 'File: ' . $e->getFile() . ' (Line: ' . $e->getLine() . ")\n";
    $errorMsg .= 'Stack Trace: ' . $e->getTraceAsString();
    error_log($errorMsg);
    
    // Show detailed error for debugging
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        $error = '<div style="background:#ffebee;padding:20px;border-radius:5px;margin:20px 0;">';
        $error .= '<h3 style="color:#c62828;">Debug Information</h3>';
        $error .= '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        $error .= '<p><strong>File:</strong> ' . $e->getFile() . ' (Line: ' . $e->getLine() . ')</p>';
        $error .= '<p><strong>Requested Slug:</strong> ' . htmlspecialchars($slug) . '</p>';
        $error .= '<h4>Available Services:</h4><ul>';
        
        try {
            $allServices = $pdo->query("SELECT slug, title FROM services WHERE status = 1 LIMIT 5")->fetchAll();
            foreach ($allServices as $s) {
                $error .= '<li><a href="service-details.php?slug=' . urlencode($s['slug']) . '">' . 
                         htmlspecialchars($s['title']) . ' (' . htmlspecialchars($s['slug']) . ')</a></li>';
            }
        } catch (Exception $e) {
            $error .= '<li>Could not fetch services: ' . htmlspecialchars($e->getMessage()) . '</li>';
        }
        
        $error .= '</ul></div>';
    } else {
        $error = 'An error occurred while loading the service details. Please try again later.';
    }
}

// Include header

// Include header
include 'includes/header.php';
?>

<!-- Service Details Section -->
<section class="service-details section-padding">
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="services.php">Services</a></li>
                            <?php 
                            if (!empty($service['category_names'])): 
                                $categorySlugs = explode(',', $service['category_slugs']);
                                $categoryNames = explode(', ', $service['category_names']);
                                foreach ($categorySlugs as $index => $slug): 
                                    if (!empty($slug) && isset($categoryNames[$index])): 
                            ?>
                                <li class="breadcrumb-item">
                                    <a href="services.php?category=<?php echo htmlspecialchars(trim($slug)); ?>">
                                        <?php echo htmlspecialchars(trim($categoryNames[$index])); ?>
                                    </a>
                                </li>
                            <?php 
                                    endif;
                                endforeach; 
                            endif; 
                            ?>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($service['title']); ?></li>
                        </ol>
                    </nav>
                    
                    <div class="service-content">
                        <h1 class="mb-4"><?php echo htmlspecialchars($service['title']); ?></h1>
                        
                        <?php if (!empty($service['image'])): ?>
                            <div class="service-image mb-4">
                                <img src="<?php echo htmlspecialchars($service['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($service['title']); ?>" 
                                     class="img-fluid rounded-3 shadow">
                            </div>
                        <?php endif; ?>
                        
                        <div class="service-meta mb-4">
                            <div class="d-flex flex-wrap gap-3">
                                <?php if (!empty($service['duration'])): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-clock text-primary me-2"></i>
                                        <span><?php echo format_duration($service['duration']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($service['price'])): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-tag text-primary me-2"></i>
                                        <span>Starts at <?php echo htmlspecialchars($service['price']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="service-description mb-5">
                            <?php echo nl2br(htmlspecialchars($service['description'])); ?>
                        </div>
                        
                        <?php if (!empty($service['features'])): ?>
                            <div class="service-features mb-5">
                                <h3 class="h4 mb-3">Service Features</h3>
                                <div class="row">
                                    <?php 
                                    $features = explode("\n", $service['features']);
                                    foreach ($features as $feature): 
                                        if (trim($feature) !== ''):
                                    ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex">
                                                <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                                                <span><?php echo htmlspecialchars(trim($feature)); ?></span>
                                            </div>
                                        </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($service['additional_info'])): ?>
                            <div class="additional-info mb-5">
                                <h3 class="h4 mb-3">Additional Information</h3>
                                <div class="content">
                                    <?php echo nl2br(htmlspecialchars($service['additional_info'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="service-sidebar">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h3 class="h5 mb-4">Book This Service</h3>
                                <a href="booking.php?service=<?php echo $service['id']; ?>" class="btn btn-primary w-100 mb-3">
                                    <i class="far fa-calendar-alt me-2"></i> Book Now
                                </a>
                                <a href="contact.php" class="btn btn-outline-primary w-100">
                                    <i class="far fa-envelope me-2"></i> Enquire Now
                                </a>
                            </div>
                        </div>
                        
                        <?php if (!empty($relatedServices)): ?>
                            <div class="related-services">
                                <h3 class="h5 mb-4">Related Services</h3>
                                <div class="list-group">
                                    <?php foreach ($relatedServices as $related): ?>
                                        <a href="service-details.php?slug=<?php echo $related['slug']; ?>" 
                                           class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($related['thumbnail'])): ?>
                                                    <img src="<?php echo htmlspecialchars($related['thumbnail']); ?>" 
                                                         alt="<?php echo htmlspecialchars($related['title']); ?>"
                                                         class="img-fluid rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($related['title']); ?></h6>
                                                    <?php if (!empty($related['price'])): ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($related['price']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
