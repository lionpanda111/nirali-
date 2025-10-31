<?php
// Set page title and meta description
$page_title = 'Course Details - Nirali Makeup Studio';
$meta_description = 'View detailed information about our professional makeup courses. Learn about course content, duration, and pricing.';
$meta_keywords = 'makeup course details, professional makeup training, beauty courses, makeup classes';

// Include config and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if slug is provided
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: academy.php');
    exit();
}

$slug = $_GET['slug'];
$course = null;

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE slug = ? AND status = 1 LIMIT 1");
    $stmt->execute([$slug]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        header('Location: academy.php');
        exit();
    }
    
    // Update page title and meta description with course details
    $page_title = $course['title'] . ' - Nirali Makeup Academy';
    $meta_description = $course['short_description'] . ' ' . $course['description'];
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: academy.php');
    exit();
}

// Include header
include 'includes/header.php';
?>

<!-- Course Details Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="academy.php">Academy</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['title']); ?></li>
                    </ol>
                </nav>
                
                <div class="card border-0 shadow-sm mb-5">
                    <?php if (!empty($course['image'])): ?>
                        <img src="<?php echo htmlspecialchars($course['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>" style="max-height: 400px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0"><?php echo htmlspecialchars($course['title']); ?></h1>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($course['duration']); ?></span>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-primary mb-3">Course Overview</h4>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Duration</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($course['duration']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-money-bill-wave text-primary me-2"></i>
                                    <div>
                                        <h6 class="mb-0">Course Fee</h6>
                                        <p class="mb-0">₹<?php echo number_format($course['price'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                            <a href="contact.php?course=<?php echo urlencode($course['title']); ?>" class="btn btn-primary btn-lg px-4 me-md-2">
                                <i class="fas fa-envelope me-2"></i> Enquire Now
                            </a>
                            <a href="academy.php" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i> Back to Courses
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Course Curriculum -->
                <div class="card border-0 shadow-sm mb-5">
                    <div class="card-header bg-white">
                        <h3 class="h5 mb-0">Course Curriculum</h3>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="curriculumAccordion">
                            <?php
                            // Sample curriculum - you can customize this based on your courses
                            $modules = [
                                'Module 1: Introduction to Makeup Artistry' => [
                                    'Understanding skin types and conditions',
                                    'Color theory and face shapes',
                                    'Essential makeup tools and their uses'
                                ],
                                'Module 2: Foundation Techniques' => [
                                    'Skin preparation and priming',
                                    'Foundation matching and application',
                                    'Concealing and color correcting'
                                ],
                                'Module 3: Eye Makeup Mastery' => [
                                    'Eyebrow shaping and filling',
                                    'Eyeshadow blending techniques',
                                    'Eyeliner and mascara application'
                                ],
                                'Module 4: Complete Looks' => [
                                    'Day to night makeup transformation',
                                    'Bridal makeup techniques',
                                    'Photoshoot and special occasion makeup'
                                ]
                            ];
                            
                            $moduleCount = 0;
                            foreach ($modules as $moduleTitle => $topics):
                                $moduleCount++;
                            ?>
                                <div class="accordion-item border-0 mb-2">
                                    <h2 class="accordion-header" id="heading<?php echo $moduleCount; ?>">
                                        <button class="accordion-button collapsed shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $moduleCount; ?>" aria-expanded="false" aria-controls="collapse<?php echo $moduleCount; ?>">
                                            <?php echo $moduleTitle; ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $moduleCount; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $moduleCount; ?>" data-bs-parent="#curriculumAccordion">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled">
                                                <?php foreach ($topics as $topic): ?>
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-primary me-2"></i>
                                                        <?php echo $topic; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
