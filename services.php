<?php
// Set page title and description
$page_title = 'Our Services - Nirali Makeup Studio';
$meta_description = 'Explore our wide range of professional makeup and beauty services at Nirali Makeup Studio. From bridal to party makeup, we have you covered.';
$meta_keywords = 'makeup services, bridal makeup, party makeup, beauty treatments, hair styling, makeup artist services';

// Include config and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/services-functions.php';
require_once 'includes/service-helpers.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize database connection
$pdo = getDBConnection();
if ($pdo === null) {
    die('Could not connect to the database. Please try again later.');
}

// Get all categories with their services
try {
    $categories = get_service_categories($pdo);
    $servicesByCategory = [];

    if (!empty($categories)) {
        foreach ($categories as $category) {
            $services = get_services_by_category_id($pdo, $category['id']);
            if (!empty($services)) {
                $servicesByCategory[] = [
                    'category' => $category,
                    'services' => $services
                ];
            }
        }
    } else {
        // Fallback if no categories found
        $servicesByCategory = [];
    }
} catch (Exception $e) {
    error_log("Error loading services: " . $e->getMessage());
    $servicesByCategory = [];
}

// Include header
include 'includes/header.php';
?>


<!-- Services Section -->
<section class="services-section section-padding">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10 text-center" data-aos="fade-up">
                <h2 class="mb-3">Our <span class="text-primary">Beauty</span> Services</h2>
                <p class="lead mb-5" style="max-width: 800px; margin: 0 auto;">Discover our comprehensive range of professional makeup and beauty services designed to enhance your natural beauty</p>
                <div class="divider mx-auto mb-5" style="width: 80px; height: 4px; background: #f8b195; opacity: 0.7;"></div>
            </div>
        </div>
        
        <?php if (empty($servicesByCategory)): ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <p class="mb-0">No services available at the moment. Please check back later.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Service Tabs -->
            <div class="row">
                <div class="col-lg-3" data-aos="fade-right">
                    <div class="nav flex-column nav-pills service-tabs" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <?php foreach ($servicesByCategory as $index => $categoryGroup): 
                            $category = $categoryGroup['category'];
                            $active = $index === 0 ? 'active' : '';
                            $icon = get_category_icon($category['name']);
                        ?>
                            <button class="nav-link <?php echo $active; ?>" 
                                    id="tab-<?php echo $category['id']; ?>" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#category-<?php echo $category['id']; ?>" 
                                    type="button" 
                                    role="tab">
                                <i class="<?php echo $icon; ?> me-2"></i> <?php echo htmlspecialchars($category['name']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="service-booking-card bg-light p-4 mt-4 rounded-3" data-aos="fade-up">
                        <h4 class="h5 mb-3">Need Help?</h4>
                        <p class="small">Not sure which service is right for you? Our beauty experts are here to help you choose the perfect treatment.</p>
                        <a href="contact.php" class="btn btn-primary btn-sm w-100">Contact Us</a>
                    </div>
                </div>
            
                <div class="col-lg-9 mt-4 mt-lg-0" data-aos="fade-left">
                    <div class="tab-content" id="v-pills-tabContent">
                        <?php foreach ($servicesByCategory as $index => $categoryGroup): 
                            $category = $categoryGroup['category'];
                            $services = $categoryGroup['services'];
                            $active = $index === 0 ? 'show active' : '';
                        ?>
                            <div class="tab-pane fade <?php echo $active; ?>" 
                                 id="category-<?php echo $category['id']; ?>" 
                                 role="tabpanel" 
                                 aria-labelledby="tab-<?php echo $category['id']; ?>">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <h3 class="mb-3"><?php echo htmlspecialchars($category['name']); ?></h3>
                                        <?php if (!empty($category['description'])): ?>
                                            <p class="lead"><?php echo nl2br(htmlspecialchars($category['description'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php foreach ($services as $service): 
                                        $serviceClass = strtolower(str_replace(' ', '-', $service['title']));
                                    ?>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="service-card card h-100 border-0 shadow-sm overflow-hidden">
                                                <div class="service-card-img position-relative">
                                                    <?php if (!empty($service['image'])): ?>
                                                        <img src="<?php echo htmlspecialchars($service['image']); ?>" 
                                                             class="card-img-top" 
                                                             alt="<?php echo htmlspecialchars($service['title']); ?>">
                                                    <?php else: ?>
                                                        <div class="service-card-img-placeholder bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-spa text-muted fa-3x"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="card-body p-4">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($service['title']); ?></h5>
                                                        <?php if (!empty($service['price'])): ?>
                                                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                                                $<?php echo number_format($service['price'], 2); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <?php if (!empty($service['duration'])): ?>
                                                        <div class="service-meta mb-3">
                                                            <span class="text-muted small">
                                                                <i class="far fa-clock me-1"></i> 
                                                                <?php echo format_duration($service['duration']); ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <p class="card-text text-muted small">
                                                        <?php 
                                                            $desc = !empty($service['short_description']) ? 
                                                                $service['short_description'] : 
                                                                (strlen($service['description']) > 100 ? 
                                                                    substr(strip_tags($service['description']), 0, 100) . '...' : 
                                                                    strip_tags($service['description']));
                                                            echo htmlspecialchars($desc);
                                                        ?>
                                                    </p>
                                                    
                                                    <div class="service-features mt-3 mb-3">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-check-circle text-primary me-2"></i>
                                                            <span class="small">Professional Service</span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-check-circle text-primary me-2"></i>
                                                            <span class="small">Premium Products</span>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-auto">
                                                        <a href="service-details.php?slug=<?php echo urlencode($service['slug']); ?>" 
                                                           class="btn btn-primary btn-sm w-100">
                                                            View Details
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    
                    <!-- Party Makeup -->
                    <div class="tab-pane fade" id="party" role="tabpanel" aria-labelledby="party-tab">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <img src="assets/images/services/party-makeup-large.jpg" alt="Party Makeup" class="img-fluid rounded-3 shadow">
                            </div>
                            <div class="col-md-7 mt-4 mt-md-0">
                                <h3 class="mb-3">Party Makeup</h3>
                                <p class="lead">Get ready to turn heads at your next event with our glamorous party makeup services.</p>
                                <p>Whether it's a birthday celebration, anniversary, or any special occasion, our makeup artists will create a stunning look that matches your outfit and personal style. From natural glam to bold and dramatic, we've got you covered.</p>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Customized looks for any occasion</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Professional makeup application</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Long-lasting, smudge-proof products</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Group packages available</li>
                                </ul>
                                <a href="booking.php?service=party-makeup" class="btn btn-primary mt-3">Book Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hair Styling -->
                    <div class="tab-pane fade" id="hairstyling" role="tabpanel" aria-labelledby="hairstyling-tab">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <img src="assets/images/services/hair-styling-large.jpg" alt="Hair Styling" class="img-fluid rounded-3 shadow">
                            </div>
                            <div class="col-md-7 mt-4 mt-md-0">
                                <h3 class="mb-3">Hair Styling</h3>
                                <p class="lead">Complete your look with our professional hair styling services for any occasion.</p>
                                <p>Our skilled hairstylists are experts in creating beautiful, camera-ready hairstyles that complement your features and personal style. From elegant updos to flowing curls, we'll help you achieve the perfect look for your special event.</p>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Bridal hairstyling</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Party hairstyles</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Hair extensions available</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Hair accessories provided</li>
                                </ul>
                                <a href="booking.php?service=hair-styling" class="btn btn-primary mt-3">Book Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Skin Care -->
                    <div class="tab-pane fade" id="skincare" role="tabpanel" aria-labelledby="skincare-tab">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <img src="assets/images/services/skincare-large.jpg" alt="Skin Care" class="img-fluid rounded-3 shadow">
                            </div>
                            <div class="col-md-7 mt-4 mt-md-0">
                                <h3 class="mb-3">Skin Care Treatments</h3>
                                <p class="lead">Pamper your skin with our luxurious facial treatments and skincare services.</p>
                                <p>Our professional estheticians provide customized facials and skincare treatments to address your specific skin concerns. Using premium products and advanced techniques, we'll help you achieve a healthy, glowing complexion.</p>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Hydrating facials</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Anti-aging treatments</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Acne control therapy</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Skin brightening treatments</li>
                                </ul>
                                <a href="booking.php?service=skincare" class="btn btn-primary mt-3">Book Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Traditional Look -->
                    <div class="tab-pane fade" id="traditional" role="tabpanel" aria-labelledby="traditional-tab">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <img src="assets/images/services/traditional-large.jpg" alt="Traditional Look" class="img-fluid rounded-3 shadow">
                            </div>
                            <div class="col-md-7 mt-4 mt-md-0">
                                <h3 class="mb-3">Traditional Look</h3>
                                <p class="lead">Embrace your cultural heritage with our traditional makeup and styling services.</p>
                                <p>Specializing in traditional Indian bridal and party looks, our artists are skilled in creating authentic and elegant styles that honor cultural traditions while incorporating modern techniques.</p>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Indian bridal makeup</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Traditional jewelry styling</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Saree draping</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Mehendi application</li>
                                </ul>
                                <a href="booking.php?service=traditional-look" class="btn btn-primary mt-3">Book Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Photo Shoot -->
                    <div class="tab-pane fade" id="photoshoot" role="tabpanel" aria-labelledby="photoshoot-tab">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <img src="assets/images/services/photoshoot-large.jpg" alt="Photo Shoot Makeup" class="img-fluid rounded-3 shadow">
                            </div>
                            <div class="col-md-7 mt-4 mt-md-0">
                                <h3 class="mb-3">Photo Shoot Makeup</h3>
                                <p class="lead">Get camera-ready with our professional makeup services designed specifically for photography.</p>
                                <p>Our makeup artists understand the unique requirements of photography and will create a look that translates beautifully on camera, whether it's for a professional photoshoot, portfolio, or special occasion.</p>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Studio lighting appropriate makeup</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> HD makeup for close-ups</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> On-location services available</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Touch-ups throughout the shoot</li>
                                </ul>
                                <a href="booking.php?service=photoshoot-makeup" class="btn btn-primary mt-3">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-choose-us section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center" data-aos="fade-up">
                <div class="section-title mb-5">
                    <h2 class="mb-3">Why <span class="text-primary">Choose Us</span></h2>
                    <p class="lead" style="max-width: 700px; margin: 0 auto;">Experience the difference with our professional beauty services</p>
                    <div class="divider mx-auto mt-3" style="width: 80px; height: 4px; background: #f8b195; opacity: 0.7;"></div>
                </div>
                
                <div class="row g-4 justify-content-center">
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="feature-card text-center p-4 h-100 border-0 bg-white rounded-3 shadow-sm h-100">
                            <div class="feature-icon mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(248, 177, 149, 0.1);">
                                <i class="fas fa-award fa-2x text-primary"></i>
                            </div>
                            <h4 class="h5 mb-3">Certified Professionals</h4>
                            <p class="mb-0 text-muted">Our team consists of certified and experienced makeup artists and beauty experts.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-card text-center p-4 h-100 border-0 bg-white rounded-3 shadow-sm h-100">
                            <div class="feature-icon mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(248, 177, 149, 0.1);">
                                <i class="fas fa-spa fa-2x text-primary"></i>
                            </div>
                            <h4 class="h5 mb-3">Premium Products</h4>
                            <p class="mb-0 text-muted">We use only high-quality, professional-grade makeup and beauty products.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                        <div class="feature-card text-center p-4 h-100 border-0 bg-white rounded-3 shadow-sm h-100">
                            <div class="feature-icon mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(248, 177, 149, 0.1);">
                                <i class="fas fa-heart fa-2x text-primary"></i>
                            </div>
                            <h4 class="h5 mb-3">Personalized Service</h4>
                            <p class="mb-0 text-muted">Customized beauty solutions tailored to your unique style and preferences.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                        <div class="feature-card text-center p-4 h-100 border-0 bg-white rounded-3 shadow-sm h-100">
                            <div class="feature-icon mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(248, 177, 149, 0.1);">
                                <i class="fas fa-home fa-2x text-primary"></i>
                            </div>
                            <h4 class="h5 mb-3">At-Home Service</h4>
                            <p class="mb-0 text-muted">Enjoy professional beauty services in the comfort of your own home.</p>
                        </div>
                    </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section section-padding" style="background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('assets/images/cta-bg-2.jpg') no-repeat center center/cover; color: #fff;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center" data-aos="fade-up">
                <h2 class="mb-4">Ready to Experience Professional Beauty Services?</h2>
                <p class="lead mb-5">Book your appointment today and let our expert team enhance your natural beauty.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="booking.php" class="btn btn-primary btn-lg">Book Now</a>
                    <a href="contact.php" class="btn btn-outline-light btn-lg">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section section-padding bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center" data-aos="fade-up">
                <div class="section-title mb-5">
                    <h2 class="mb-3">Frequently Asked <span class="text-primary">Questions</span></h2>
                    <p class="lead" style="max-width: 700px; margin: 0 auto;">Find answers to common questions about our services</p>
                    <div class="divider mx-auto mt-3" style="width: 80px; height: 4px; background: #f8b195; opacity: 0.7;"></div>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="accordion" id="faqAccordion">
                            <!-- FAQ Item 1 -->
                            <div class="accordion-item mb-3 border-0 bg-light rounded-3 overflow-hidden shadow-sm" data-aos="fade-up">
                                <h3 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        <i class="far fa-question-circle text-primary me-2"></i>
                                        How far in advance should I book my bridal makeup appointment?
                                    </button>
                                </h3>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        We recommend booking your bridal makeup appointment at least 3-6 months in advance, especially during peak wedding seasons. This ensures you secure your preferred date and time. For last-minute bookings, please contact us for availability.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- FAQ Item 2 -->
                            <div class="accordion-item mb-3 border-0 bg-light rounded-3 overflow-hidden shadow-sm" data-aos="fade-up" data-aos-delay="50">
                                <h3 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        <i class="far fa-question-circle text-primary me-2"></i>
                                        Do you offer trial sessions for bridal makeup?
                                    </button>
                                </h3>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        Yes, we highly recommend scheduling a trial session before your wedding day. This allows you to discuss your preferences, try different looks, and make any necessary adjustments. The trial session typically lasts 2-3 hours and should be scheduled 4-8 weeks before your wedding.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- FAQ Item 3 -->
                            <div class="accordion-item mb-3 border-0 bg-light rounded-3 overflow-hidden shadow-sm" data-aos="fade-up" data-aos-delay="100">
                                <h3 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        <i class="far fa-question-circle text-primary me-2"></i>
                                        What brands of makeup do you use?
                                    </button>
                                </h3>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        We use professional-grade, high-quality makeup brands such as MAC, NARS, Bobbi Brown, Estée Lauder, Huda Beauty, and Charlotte Tilbury, among others. All our products are cruelty-free and suitable for all skin types. If you have any specific allergies or preferences, please let us know in advance.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- FAQ Item 4 -->
                            <div class="accordion-item mb-3 border-0 bg-light rounded-3 overflow-hidden shadow-sm" data-aos="fade-up" data-aos-delay="150">
                                <h3 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        <i class="far fa-question-circle text-primary me-2"></i>
                                        Do you provide services for bridesmaids and family members?
                                    </button>
                                </h3>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        Absolutely! We offer makeup and hairstyling services for the entire bridal party, including bridesmaids, mothers of the bride and groom, and other family members. We recommend booking these services well in advance and can provide group discounts for parties of three or more.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- FAQ Item 5 -->
                            <div class="accordion-item mb-3 border-0 bg-light rounded-3 overflow-hidden shadow-sm" data-aos="fade-up" data-aos-delay="200">
                                <h3 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                        <i class="far fa-question-circle text-primary me-2"></i>
                                        What is your cancellation policy?
                                    </button>
                                </h3>
                                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        We require a 50% deposit to secure your booking, which is non-refundable but transferable to another date if you reschedule at least 14 days before your appointment. For cancellations made less than 48 hours before the appointment, the full service amount will be charged. We understand emergencies happen, so please contact us as soon as possible if you need to reschedule or cancel.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="250">
                            <p class="mb-4 lead">Have more questions? We're here to help!</p>
                            <a href="contact.php" class="btn btn-primary px-4 py-2">
                                <i class="fas fa-envelope me-2"></i>Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
