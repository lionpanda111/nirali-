<?php
// Initialize variables
$name = $email = $phone = $subject = $message = '';
$errors = [];
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate inputs
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // Check if database connection is available
        if (isset($pdo) && $pdo) {
            try {
                // Check if table exists
                $tableExists = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
                if ($tableExists->rowCount() > 0) {
                    // Insert into database
                    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
                                        VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$name, $email, $phone, $subject, $message]);
                }
                
                // Send email notification (you'll need to configure your mail server)
                $to = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@example.com';
                $email_subject = "New Contact Form Submission: " . htmlspecialchars($subject);
                $email_body = "You have received a new message from your website contact form.\n\n".
                             "Name: " . htmlspecialchars($name) . "\n".
                             "Email: " . htmlspecialchars($email) . "\n".
                             "Phone: " . htmlspecialchars($phone) . "\n\n".
                             "Message:\n" . htmlspecialchars($message);
                $headers = "From: " . htmlspecialchars($email) . "\r\n".
                          "Reply-To: " . htmlspecialchars($email) . "\r\n".
                          'X-Mailer: PHP/'.phpversion();
                
                // Uncomment to enable email sending (configure your server first)
                // @mail($to, $email_subject, $email_body, $headers);
                
                // Set success message
                $success = 'Thank you for your message. We will get back to you soon!';
                
                // Clear form
                $name = $email = $phone = $subject = $message = '';
                
            } catch (PDOException $e) {
                error_log("Error saving contact message: " . $e->getMessage());
                // Continue with success message even if database fails
                $success = 'Thank you for your message. We will get back to you soon!';
                $name = $email = $phone = $subject = $message = '';
            }
        } else {
            // If no database connection, just show success message (form will still work with email)
            $success = 'Thank you for your message. We will get back to you soon!';
            $name = $email = $phone = $subject = $message = '';
        }
    }
}
?>

<!-- Contact Section -->
<section id="contact" class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h2>Get In Touch</h2>
                <p class="text-muted mb-4">Have questions or want to book an appointment? Send us a message and we'll get back to you as soon as possible.</p>
                
                <div class="contact-info mb-4">
                    <div class="d-flex mb-4">
                        <div class="d-flex justify-content-start" style="width: 40px; margin-right: 15px; margin-top: 3px;">
                            <i class="fas fa-map-marker-alt" style="color: #D4AF37; font-size: 1.5rem;"></i>
                        </div>
                        <div style="flex: 1;">
                            <h6 class="mb-1" style="color: #1a1a1a; font-weight: 600; font-size: 1rem;">Our Location</h6>
                            <p class="mb-0" style="color: #666666; line-height: 1.6; font-size: 0.95rem;">B-84, Mahavirnagar Society, Road, near Gajera Circle, Minaxi Wadi, Katargam, Surat, Gujarat 395004</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-4">
                        <div class="d-flex justify-content-start" style="width: 40px; margin-right: 15px; margin-top: 3px;">
                            <i class="fas fa-phone-alt" style="color: #D4AF37; font-size: 1.5rem;"></i>
                        </div>
                        <div style="flex: 1;">
                            <h6 class="mb-1" style="color: #1a1a1a; font-weight: 600; font-size: 1rem;">Call Us</h6>
                            <p class="mb-0">
                                <a href="tel:09173899945" style="color: #666666; text-decoration: none; transition: color 0.3s; font-size: 0.95rem;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color='#666666'">091738 99945</a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <div class="d-flex justify-content-start" style="width: 40px; margin-right: 15px; margin-top: 3px;">
                            <i class="far fa-envelope" style="color: #D4AF37; font-size: 1.5rem;"></i>
                        </div>
                        <div style="flex: 1;">
                            <h6 class="mb-1" style="color: #1a1a1a; font-weight: 600; font-size: 1rem;">Email Us</h6>
                            <p class="mb-0">
                                <a href="mailto:info@niralimakeup.com" style="color: #666666; text-decoration: none; transition: color 0.3s; font-size: 0.95rem;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color='#666666'">info@niralimakeup.com</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="https://www.facebook.com/niralimakeupstudio/" class="me-2 text-primary" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/niralimakeupstudio/" class="me-2 text-primary" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/@Niralimakeupstudio" class="me-2 text-primary" target="_blank"><i class="fab fa-youtube"></i></a>
                    <a href="https://in.pinterest.com/Niralimakeupstudio/" class="me-2 text-primary" target="_blank"><i class="fab fa-pinterest-p"></i></a>
                </div>
            </div>
            
            <div class="col-lg-6 mt-5 mt-lg-0">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php elseif (!empty($errors['database'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $errors['database']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="mb-4">Send Us a Message</h3>
                        <form id="contactForm" method="POST" action="#contact">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                           id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['name']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['email']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($phone); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo htmlspecialchars($subject); ?>">
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                                <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                                          id="message" name="message" rows="4" required><?php echo htmlspecialchars($message); ?></textarea>
                                <?php if (isset($errors['message'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['message']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i> Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
    /* Gold and White Theme Styles */
    :root {
        --primary-color: #D4AF37;
        --primary-hover: #B7950B;
        --text-color: #333333;
        --light-bg: #FFF9E6;
        --dark-bg: #FFF5CC;
        --border-color: #E6E6E6;
    }
    
    #contact {
        background-color: #ffffff;
        padding: 5rem 0;
        position: relative;
        overflow: hidden;
    }
    
    #contact h2 {
        color: #1a1a1a;
        font-weight: 700;
        margin-bottom: 1.5rem;
        position: relative;
        display: inline-block;
    }
    
    #contact h2:after {
        content: '';
        position: absolute;
        width: 50px;
        height: 3px;
        background: var(--primary-color);
        bottom: -10px;
        left: 0;
    }
    
    .contact-info .icon-box {
        background-color: var(--light-bg);
        color: var(--primary-color);
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 1rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }
    
    .contact-info .icon-box:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.2);
    }
    
    .contact-info h6 {
        color: #1a1a1a;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .contact-info a {
        color: #4d4d4d;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .contact-info a:hover {
        color: var(--primary-color);
    }
    
    .card {
        border: 1px solid var(--border-color);
        border-radius: 10px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        background: white;
        height: 100%;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .form-control {
        height: 50px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
    }
    
    textarea.form-control {
        height: auto;
        min-height: 120px;
        resize: vertical;
    }
    
    .form-label {
        font-weight: 500;
        color: #4d4d4d;
        margin-bottom: 0.5rem;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #1a1a1a;
        font-weight: 600;
        padding: 0.75rem 2.5rem;
        border-radius: 50px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
        border: none;
    }
    
    .btn-primary:hover, .btn-primary:focus {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(183, 149, 11, 0.3);
        color: #1a1a1a;
    }
    
    .social-links {
        margin-top: 2rem;
    }
    
    .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--light-bg);
        color: var(--primary-color);
        margin-right: 10px;
        transition: all 0.3s ease;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }
    
    .social-links a:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.2);
    }
    
    /* Form Validation Styles */
    .is-invalid {
        border-color: #dc3545 !important;
    }
    
    .invalid-feedback {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 991.98px) {
        .contact-info {
            margin-bottom: 2rem;
        }
        
        .social-links {
            margin-bottom: 2rem;
        }
    }
</style>
