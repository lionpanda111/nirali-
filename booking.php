<?php
// Set page title
$page_title = 'Book an Appointment';
$meta_description = 'Book your appointment with Nirali Makeup Studio for professional makeup and beauty services.';
$meta_keywords = 'book appointment, makeup booking, beauty services, salon booking, makeup artist booking';

// Include header
include 'includes/header.php';

// Include database connection
require_once 'includes/config.php';

// Initialize variables
$errors = [];
$success = false;
$booking = [
    'name' => '',
    'phone' => '',
    'email' => '',
    'service_id' => '',
    'booking_date' => date('Y-m-d'),
    'booking_time' => '',
    'notes' => ''
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $booking['name'] = trim($_POST['name'] ?? '');
    $booking['phone'] = trim($_POST['phone'] ?? '');
    $booking['email'] = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $booking['service_id'] = (int)($_POST['service_id'] ?? 0);
    $booking['booking_date'] = trim($_POST['booking_date'] ?? '');
    $booking['booking_time'] = trim($_POST['booking_time'] ?? '');
    $booking['notes'] = trim($_POST['notes'] ?? '');

    // Validate inputs
    if (empty($booking['name'])) {
        $errors[] = 'Please enter your name';
    }
    
    if (empty($booking['phone'])) {
        $errors[] = 'Please enter your phone number';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $booking['phone'])) {
        $errors[] = 'Please enter a valid phone number';
    }
    
    if (empty($booking['email']) || !filter_var($booking['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($booking['service_id'])) {
        $errors[] = 'Please select a service';
    }
    
    if (empty($booking['booking_date'])) {
        $errors[] = 'Please select a date';
    }
    
    if (empty($booking['booking_time'])) {
        $errors[] = 'Please select a time';
    }

    // If no errors, save to database
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("INSERT INTO bookings (name, phone, email, service_id, booking_date, booking_time, notes, status, created_at) 
                                  VALUES (:name, :phone, :email, :service_id, :booking_date, :booking_time, :notes, 'pending', NOW())");
            
            $stmt->execute([
                ':name' => $booking['name'],
                ':phone' => $booking['phone'],
                ':email' => $booking['email'],
                ':service_id' => $booking['service_id'],
                ':booking_date' => $booking['booking_date'],
                ':booking_time' => $booking['booking_time'],
                ':notes' => $booking['notes']
            ]);
            
            $success = true;
            
            // Clear form
            $booking = array_fill_keys(array_keys($booking), '');
            
        } catch (PDOException $e) {
            error_log("Booking error: " . $e->getMessage());
            $errors[] = 'An error occurred while processing your booking. Please try again later.';
        }
    }
}

// Fetch active services for dropdown
try {
    $pdo = getDBConnection();
    
    if ($pdo === null) {
        throw new Exception("Failed to connect to database");
    }
    
    // First, check if services table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'services'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Try to create the table if it doesn't exist
        $createTableSql = file_get_contents('sql/services_tables.sql');
        $pdo->exec($createTableSql);
        error_log("Created services table");
    }
    
    // Now fetch services with proper price handling
    $services = $pdo->query("SELECT id, title, COALESCE(price, 0) as price FROM services WHERE status = 1 ORDER BY display_order, title")->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the number of services found
    error_log("Number of active services found: " . count($services));
    
    // If no services found, insert sample data
    if (empty($services)) {
        error_log("No services found, inserting sample data");
        $sampleData = [
            ['Complete Bridal Package', 300.00],
            ['Evening Glam Makeup', 100.00],
            ['Updo Hairstyle', 75.00],
            ['Hydrating Facial', 80.00],
            ['Traditional Bridal Look', 250.00]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO services (title, price, status) VALUES (?, ?, 1)");
        foreach ($sampleData as $service) {
            $stmt->execute([$service[0], $service[1]]);
        }
        
        // Fetch services again
        $services = $pdo->query("SELECT id, title, price FROM services WHERE status = 1 ORDER BY display_order, title")->fetchAll(PDO::FETCH_ASSOC);
        error_log("After inserting sample data, found " . count($services) . " services");
    }
    
} catch (Exception $e) {
    error_log("Error in booking page: " . $e->getMessage());
    $error_message = $e->getMessage();
    $services = [];
}
?>

<!-- Hero Section -->
<div class="booking-hero bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-5 fw-bold mb-3">Book Your Appointment</h1>
                <p class="lead mb-4">Fill out the form below to schedule your beauty session with us. We'll get back to you shortly to confirm your appointment.</p>
            </div>
        </div>
    </div>
</div>

<!-- Booking Form Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">Booking Request Received!</h4>
                        <p>Thank you for booking with us. We've received your request and will contact you shortly to confirm your appointment.</p>
                        <hr>
                        <p class="mb-0">For any questions, please call us at <a href="tel:+919999999999" class="alert-link">+91 99999 99999</a>.</p>
                    </div>
                <?php elseif (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">Please fix the following errors:</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form method="post" action="" id="bookingForm">
                            <div class="row g-3">
                                <!-- Personal Information -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($booking['name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control form-control-lg" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($booking['phone']); ?>" required>
                                        <small class="form-text text-muted">We'll only use this to contact you about your booking</small>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($booking['email']); ?>" >
                                    </div>
                                </div>
                                
                                <!-- Service Selection -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="service_id" class="form-label">Select Service <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-lg" id="service_id" name="service_id" required>
                                            <option value="">-- Select a Service --</option>
                                            <?php foreach ($services as $service): ?>
                                                <option value="<?php echo $service['id']; ?>" 
                                                    <?php echo ($booking['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($service['title']); ?>
                                                    <?php if ($service['price'] > 0): ?>
                                                        (₹<?php echo number_format($service['price']); ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Date and Time -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="booking_date" class="form-label">Preferred Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-lg" id="booking_date" 
                                               name="booking_date" min="<?php echo date('Y-m-d'); ?>" 
                                               value="<?php echo htmlspecialchars($booking['booking_date']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="booking_time" class="form-label">Preferred Time <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-lg" id="booking_time" name="booking_time" required>
                                            <option value="">-- Select Time --</option>
                                            <?php
                                            // Generate time slots from 9 AM to 8 PM
                                            $start = strtotime('09:00');
                                            $end = strtotime('20:00');
                                            $interval = 30 * 60; // 30 minutes in seconds
                                            
                                            for ($time = $start; $time <= $end; $time += $interval) {
                                                $time_value = date('H:i', $time);
                                                $time_display = date('h:i A', $time);
                                                $selected = ($booking['booking_time'] === $time_value) ? 'selected' : '';
                                                echo "<option value=\"$time_value\" $selected>$time_display</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Additional Notes -->
                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="notes" class="form-label">Special Requests or Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                  placeholder="Any special requirements or notes for your appointment..."><?php echo htmlspecialchars($booking['notes']); ?></textarea>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-calendar-check me-2"></i> Book Appointment
                                    </button>
                                </div>
                                
                                <!-- Contact Information -->
                                <div class="col-12 text-center mt-4">
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-phone-alt me-2"></i> Need help? Call us at 
                                        <a href="tel:+919999999999" class="text-decoration-none">+91 99999 99999</a>
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <h2 class="display-6 fw-bold mb-3">Why Choose Nirali Makeup Studio?</h2>
                <p class="lead">We're committed to providing you with the best beauty experience</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4 text-center" data-aos="fade-up">
                <div class="p-4 h-100">
                    <div class="icon-box bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-award fa-2x text-primary"></i>
                    </div>
                    <h4 class="h5 mb-2">Professional Artists</h4>
                    <p class="text-muted mb-0">Certified and experienced makeup artists who understand your unique beauty needs.</p>
                </div>
            </div>
            <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="100">
                <div class="p-4 h-100">
                    <div class="icon-box bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-spa fa-2x text-primary"></i>
                    </div>
                    <h4 class="h5 mb-2">Premium Products</h4>
                    <p class="text-muted mb-0">We use only high-quality, professional-grade products for flawless results.</p>
                </div>
            </div>
            <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="p-4 h-100">
                    <div class="icon-box bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-heart fa-2x text-primary"></i>
                    </div>
                    <h4 class="h5 mb-2">Personalized Service</h4>
                    <p class="text-muted mb-0">Customized beauty solutions tailored to your individual style and preferences.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <h2 class="display-6 fw-bold mb-3">What Our Clients Say</h2>
                <p class="lead">Don't just take our word for it - hear from our satisfied clients</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="testimonial-slider">
                    <?php
                    // Fetch testimonials from database
                    try {
                        $testimonials = $pdo->query("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($testimonials)) {
                            // Fallback testimonials if none in database
                            $testimonials = [
                                [
                                    'client_name' => 'Priya Sharma',
                                    'content' => 'Nirali Makeup Studio is amazing! The artists are so talented and professional. I looked stunning on my wedding day!',
                                    'rating' => 5
                                ],
                                [
                                    'client_name' => 'Riya Patel',
                                    'content' => 'Best makeup experience ever! The team is so friendly and really listens to what you want. Highly recommended!',
                                    'rating' => 5
                                ],
                                [
                                    'client_name' => 'Anjali Mehta',
                                    'content' => 'I always get compliments on my makeup when I go to Nirali. Their attention to detail is incredible!',
                                    'rating' => 5
                                ]
                            ];
                        }
                        
                        foreach ($testimonials as $testimonial):
                            $rating = $testimonial['rating'] ?? 5;
                            $stars = str_repeat('<i class="fas fa-star text-warning"></i>', $rating);
                    ?>
                    <div class="testimonial-item text-center p-4">
                        <div class="mb-3">
                            <?php echo $stars; ?>
                        </div>
                        <p class="lead mb-4">"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                        <h5 class="mb-1"><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                    </div>
                    <?php endforeach; ?>
                    <?php } catch (Exception $e) {
                        error_log("Error fetching testimonials: " . $e->getMessage());
                    } ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <h2 class="display-6 fw-bold mb-3">Frequently Asked Questions</h2>
                <p class="lead">Find answers to common questions about our booking process</p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                How far in advance should I book my appointment?
                            </button>
                        </h3>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" 
                             data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We recommend booking at least 1-2 weeks in advance to ensure availability, especially for weekends and special occasions. 
                                For weddings and large events, we suggest booking 2-3 months in advance.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                What is your cancellation policy?
                            </button>
                        </h3>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" 
                             data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We require at least 24 hours' notice for cancellations or rescheduling. Appointments cancelled with less than 24 hours' notice 
                                may be subject to a cancellation fee of 50% of the service cost. No-shows will be charged the full amount of the service.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Do you offer bridal makeup trials?
                            </button>
                        </h3>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" 
                             data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we highly recommend scheduling a bridal trial before your wedding day. This allows us to create your perfect look and 
                                make any necessary adjustments. Trials typically last 2-3 hours and can be scheduled at our studio.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h3 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                What payment methods do you accept?
                            </button>
                        </h3>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" 
                             data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept cash, credit/debit cards, UPI payments, and mobile wallets. A deposit may be required at the time of booking for certain services.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.booking-hero {
    background: linear-gradient(135deg, #f9f3e9 0%, #fff9f0 100%);
    padding: 5rem 0;
}

.icon-box {
    background: var(--primary-color);
    color: white;
    transition: all 0.3s ease;
}

.testimonial-item {
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    margin: 0 15px;
    transition: transform 0.3s ease;
    height: 100%;
}

.testimonial-item:hover {
    transform: translateY(-5px);
}

.testimonial-slider .slick-dots {
    bottom: -40px;
}

.testimonial-slider .slick-dots li button:before {
    font-size: 12px;
    color: var(--primary-color);
}

.testimonial-slider .slick-dots li.slick-active button:before {
    color: var(--primary-color);
}

.accordion-button:not(.collapsed) {
    background-color: rgba(212, 175, 55, 0.1);
    color: var(--primary-color);
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
    border-color: var(--primary-color);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    padding: 0.75rem 2rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
    border-color: var(--primary-hover);
    transform: translateY(-2px);
}

.card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .booking-hero {
        padding: 3rem 0;
    }
    
    .btn-primary {
        width: 100%;
    }
}
</style>

<!-- Add Slick Carousel for testimonials -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Slick Carousel for testimonials
    $('.testimonial-slider').slick({
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 5000,
        arrows: false,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });
    
    // Form validation
    $('#bookingForm').on('submit', function(e) {
        let isValid = true;
        const form = this;
        
        // Reset previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate name
        if ($('#name').val().trim() === '') {
            showError($('#name'), 'Please enter your name');
            isValid = false;
        }
        
        // Validate phone
        const phone = $('#phone').val().trim();
        if (phone === '') {
            showError($('#phone'), 'Please enter your phone number');
            isValid = false;
        } else if (!/^[0-9]{10,15}$/.test(phone)) {
            showError($('#phone'), 'Please enter a valid phone number');
            isValid = false;
        }
        
        // Validate email
        const email = $('#email').val().trim();
        if (email === '') {
            showError($('#email'), 'Please enter your email address');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError($('#email'), 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate service
        if ($('#service_id').val() === '') {
            showError($('#service_id'), 'Please select a service');
            isValid = false;
        }
        
        // Validate date
        if ($('#booking_date').val() === '') {
            showError($('#booking_date'), 'Please select a date');
            isValid = false;
        } else {
            const selectedDate = new Date($('#booking_date').val());
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                showError($('#booking_date'), 'Please select a future date');
                isValid = false;
            }
        }
        
        // Validate time
        if ($('#booking_time').val() === '') {
            showError($('#booking_time'), 'Please select a time');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });
    
    function showError(element, message) {
        element.addClass('is-invalid');
        element.after('<div class="invalid-feedback">' + message + '</div>');
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Disable past dates in date picker
    const today = new Date().toISOString().split('T')[0];
    $('#booking_date').attr('min', today);
    
    // Initialize AOS animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
