<?php
// Initialize testimonials array
$testimonials = [];

// Get testimonials from database if connection is available
if (isset($pdo) && $pdo) {
    try {
        // Check if testimonials table exists and has status column
        $checkTable = $pdo->query("SHOW TABLES LIKE 'testimonials'");
        if ($checkTable->rowCount() > 0) {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM testimonials LIKE 'status'");
            if ($checkColumn->rowCount() > 0) {
                $stmt = $pdo->query("SELECT * FROM testimonials WHERE status = 1 ORDER BY created_at DESC LIMIT 3");
            } else {
                $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 3");
            }
        } else {
            $testimonials = [];
            return; // Exit if testimonials table doesn't exist
        }
        $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching testimonials: " . $e->getMessage());
        // Continue with empty testimonials array
    }
}
?>

<!-- Testimonials Section -->
<section id="testimonials" class="py-5" style="background-color: #FFF9C4;">
    <div class="container">
        <h2 class="text-center mb-5">What Our Clients Say</h2>
        
        <?php if (!empty($testimonials)): ?>
            <div class="row g-4">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm" style="background-color: white; border-radius: 15px; overflow: hidden; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div class="card-body text-center">
                                <div class="mb-3" style="color: #FFD700;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $testimonial['rating']): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-warning"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="card-text mb-4">"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                                <div class="d-flex align-items-center justify-content-center">
                                    <?php if (!empty($testimonial['client_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($testimonial['client_image']); ?>" 
                                             class="rounded-circle me-3" 
                                             alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>"
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" 
                                             style="width: 60px; height: 60px;">
                                            <i class="fas fa-user text-white" style="font-size: 1.5rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="text-start">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($testimonial['client_name']); ?></h6>
                                        <?php if (!empty($testimonial['client_title'])): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($testimonial['client_title']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="testimonials.php" class="btn" style="background-color: #FFD700; color: #333; border: none; font-weight: 600; padding: 0.75rem 2rem; border-radius: 50px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">View All Testimonials</a>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <p class="text-muted">No testimonials available at the moment.</p>
                <a href="contact.php" class="btn" style="background-color: #FFD700; color: #333; border: none; font-weight: 600; padding: 0.75rem 2rem; border-radius: 50px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Be the first to review</a>
            </div>
        <?php endif; ?>
    </div>
</section>
