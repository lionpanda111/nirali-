<?php
// Get active courses from database
$courses = [];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM courses WHERE status = 1 ORDER BY display_order, title");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching courses: " . $e->getMessage());
}
?>

<!-- Courses Section -->
<section id="courses" class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5" data-aos="fade-up">
                <h2 class="mb-3">Our <span class="text-primary">Courses</span></h2>
                <p class="lead">Professional makeup training programs to kickstart your career</p>
                <div class="divider mx-auto"></div>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if (count($courses) > 0): ?>
                <?php foreach ($courses as $course): 
                    $image_path = !empty($course['image']) ? $course['image'] : 'assets/images/course-default.jpg';
                    $duration = !empty($course['duration']) ? $course['duration'] : 'Flexible';
                    $price = $course['price'] > 0 ? '₹' . number_format($course['price'], 2) : 'Contact for Pricing';
                ?>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card h-100 border-0 shadow-sm overflow-hidden">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($image_path); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>" style="height: 200px; object-fit: cover;">
                                <?php if ($course['is_featured']): ?>
                                    <span class="position-absolute top-0 end-0 m-2 bg-warning text-dark px-2 py-1 small rounded">Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars($course['short_description'] ?? substr($course['description'], 0, 100) . '...'); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-light text-dark"><i class="far fa-clock me-1"></i> <?php echo htmlspecialchars($duration); ?></span>
                                    <span class="fw-bold text-primary"><?php echo $price; ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <a href="course-details.php?slug=<?php echo urlencode($course['slug']); ?>" class="btn btn-outline-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">No courses available at the moment. Please check back later.</div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="#contact" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-envelope me-2"></i> Enquire About Courses
            </a>
        </div>
    </div>
</section>
