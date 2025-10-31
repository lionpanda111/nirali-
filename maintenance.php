<?php
/**
 * Maintenance Mode
 * Rename this file to index.php to enable maintenance mode
 */

// Allow access from your IP (replace with your IP)
$allowed_ips = ['127.0.0.1', '::1', 'YOUR_IP_ADDRESS'];

// Check if current IP is allowed
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    // Set 503 Service Unavailable header
    header('HTTP/1.1 503 Service Unavailable');
    header('Retry-After: 3600'); // 1 hour
    
    // Output maintenance page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maintenance Mode - Nirali Makeup Studio</title>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                text-align: center;
            }
            .maintenance-container {
                margin-top: 100px;
                padding: 40px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            h1 {
                color: #d4af37;
                margin-bottom: 20px;
            }
            .logo {
                max-width: 200px;
                margin-bottom: 30px;
            }
            .contact-info {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #eee;
            }
            .social-links {
                margin-top: 20px;
            }
            .social-links a {
                display: inline-block;
                margin: 0 10px;
                color: #d4af37;
                font-size: 20px;
            }
        </style>
    </head>
    <body>
        <div class="maintenance-container">
            <img src="/assets/images/LOGO.png" alt="Nirali Makeup Studio" class="logo">
            <h1>We'll Be Back Soon!</h1>
            <p>We're currently performing scheduled maintenance to improve our services.</p>
            <p>We apologize for the inconvenience and appreciate your patience.</p>
            
            <div class="contact-info">
                <p>For urgent inquiries, please contact us at:</p>
                <p><strong>Email:</strong> <a href="mailto:contact@niralimakeupstudio.com">contact@niralimakeupstudio.com</a></p>
                <p><strong>Phone:</strong> <a href="tel:+919876543210">+91 98765 43210</a></p>
                
                <div class="social-links">
                    <p>Follow us on social media for updates:</p>
                    <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Font Awesome for icons -->
        <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>
    </body>
    </html>
    <?php
    exit();
}

// If IP is allowed, redirect to the main site
header('Location: index.php');
exit();
