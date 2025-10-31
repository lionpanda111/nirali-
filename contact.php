<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Initialize variables
$name = $email = $phone = $subject = $message = '';
$success = $error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $subject = 'Contact Form Submission';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    // If no validation errors, process the form
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            
      
            
            // Insert into database
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages 
                (name, email, phone, subject, message,status, is_read, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?,'new', 0, NOW(), NOW())
            ");
            
            if ($stmt->execute([
                $name, 
                $email, 
                $phone, 
                $subject, 
                $message
            ])) {
                $success = 'Thank you for your message! We will get back to you soon.';
                
                // Send email notification to admin
                $admin_email = 'your-email@example.com'; // Replace with your email
                $email_subject = "New Contact Form Submission: $subject";
                $email_body = "You have received a new message from your website contact form.\n\n".
                             "Name: $name\n".
                             "Email: $email\n".
                             "Phone: " . ($phone ?: 'Not provided') . "\n\n".
                             "Message:\n$message";
                
                // Uncomment to enable email sending
                // mail($admin_email, $email_subject, $email_body, "From: noreply@".$_SERVER['HTTP_HOST']);
                
                // Clear form
                $name = $email = $phone = $subject = $message = '';
                
            } else {
                $error = 'There was an error sending your message. Please try again.';
            }
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $error = 'There was a problem sending your message. Please try again later.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!-- Page Header -->
<section class="page-header bg-light py-5 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4">Contact Us</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Contact</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-5">
                        <h2 class="h4 mb-4">Get in Touch</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form id="contactForm" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>">
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($message); ?></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-5">
                        <h2 class="h4 mb-4">Contact Information</h2>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h6 mb-1">Our Location</h3>
                                <p class="mb-0 text-muted">123 Beauty Street, Makeup City, MC 12345</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-phone-alt text-primary"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h6 mb-1">Phone Number</h3>
                                <p class="mb-0">
                                    <a href="tel:+1234567890" class="text-decoration-none text-dark">+1 (234) 567-890</a>
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h6 mb-1">Email Address</h3>
                                <p class="mb-0">
                                    <a href="mailto:info@makeupstudio.com" class="text-decoration-none text-dark">info@makeupstudio.com</a>
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-clock text-primary"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h3 class="h6 mb-1">Working Hours</h3>
                                <p class="mb-0">
                                    <span class="d-block">Monday - Friday: 9:00 AM - 7:00 PM</span>
                                    <span class="d-block">Saturday: 10:00 AM - 5:00 PM</span>
                                    <span class="d-block">Sunday: Closed</span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <h3 class="h6 mb-3">Follow Us</h3>
                            <div class="d-flex">
                                <a href="https://www.facebook.com/niralimakeupstudio/" target="_blank" class="btn btn-outline-primary btn-sm me-2" style="color: #1877F2; border-color: #1877F2;">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://www.instagram.com/niralimakeupstudio/" target="_blank" class="btn btn-outline-primary btn-sm me-2" style="color: #E1306C; border-color: #E1306C;">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="https://www.youtube.com/@Niralimakeupstudio" target="_blank" class="btn btn-outline-primary btn-sm me-2" style="color: #FF0000; border-color: #FF0000;">
                                    <i class="fab fa-youtube"></i>
                                </a>
                                <a href="https://in.pinterest.com/Niralimakeupstudio/" target="_blank" class="btn btn-outline-primary btn-sm" style="color: #E60023; border-color: #E60023;">
                                    <i class="fab fa-pinterest-p"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
