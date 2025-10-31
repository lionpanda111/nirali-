<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Get site settings
$site_title = 'Nirali Makeup Studio';
$page_title = $page_title ?? 'Professional Beauty Services';

// Get current page URL
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?php echo $meta_description ?? 'Nirali Makeup Studio offers professional makeup and beauty services. Book your appointment today for a stunning look.'; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords ?? 'makeup, beauty, salon, bridal makeup, party makeup, makeup artist'; ?>">
    <meta name="author" content="Nirali Makeup Studio">
    <meta name="theme-color" content="#d4af37">
    
    <title><?php echo $page_title . ' | ' . $site_title; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Glide.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.5.2/dist/css/glide.core.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.5.2/dist/css/glide.theme.min.css">
    
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #D4AF37; /* Gold */
            --primary-hover: #B7950B;
            --text-color: #333333;
            --light-bg: #FFF9C4;
            --dark-bg: #FFF59D;
        }
        
        body {
            color: var(--text-color);
        }
        
        .navbar {
            background-color: white !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            color: var(--text-color) !important;
            font-weight: 700;
        }
        
        .navbar-brand img {
            height: 50px;
            width: auto;
            margin-right: 10px;
        }
        
        /* Mobile Menu Toggle Button */
        .navbar-toggler {
            width: 40px;
            height: 30px;
            padding: 0;
            position: relative;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            background: transparent;
        }
        
        .navbar-toggler span {
            display: block;
            width: 100%;
            height: 3px;
            background-color: var(--text-color);
            transition: all 0.3s ease;
            border-radius: 3px;
        }
        
        .navbar-toggler:not(.collapsed) span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .navbar-toggler:not(.collapsed) span:nth-child(2) {
            opacity: 0;
        }
        
        .navbar-toggler:not(.collapsed) span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }
        
        /* Close button styles */
        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            line-height: 1;
            padding: 0.5rem;
            cursor: pointer;
            color: #333;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }
        
        .btn-close:hover {
            opacity: 1;
            color: var(--primary-color);
        }
        
        @media (max-width: 991.98px) {
            .navbar {
                background: white !important;
                padding: 10px 15px;
                position: relative;
                z-index: 9999 !important;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .navbar-brand img {
                height: 40px;
            }
            
            .navbar-brand span {
                font-size: 1.1rem;
            }
            
            .navbar-collapse {
                position: absolute !important;
                top: 100% !important;
                left: 0 !important;
                right: 0 !important;
                background: white !important;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
                padding: 15px !important;
                border-radius: 0 0 10px 10px !important;
                margin: 0 !important;
                max-height: 80vh !important;
                overflow-y: auto !important;
                transition: all 0.3s ease-in-out !important;
            }
            
            .navbar-nav {
                margin-bottom: 15px;
                width: 100%;
            }
            
            .nav-item {
                margin: 5px 0;
                text-align: center;
            }
            
            .nav-link {
                padding: 12px 15px !important;
                border-radius: 5px;
                font-weight: 500;
                color: #333 !important;
                transition: all 0.2s ease;
            }
            
            .nav-link:hover,
            .nav-link:focus {
                background-color: rgba(0, 0, 0, 0.05);
                color: var(--primary-color) !important;
            }
            
            .navbar .d-flex {
                padding: 10px 15px;
                margin-top: 10px;
                border-top: 1px solid rgba(0, 0, 0, 0.05);
            }
            
            .navbar .btn {
                width: 100%;
                max-width: 200px;
                margin: 0 auto;
                padding: 10px 20px;
                font-weight: 600;
                border-radius: 5px;
            }
        }
        
        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            position: relative;
            margin: 0 6px;
            padding: 0.5rem 0.7rem !important;
        }
        
        .nav-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary-color);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover:after,
        .nav-link.active:after {
            width: 100%;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            color: var(--text-color);
        }
        
        .hero-section {
            background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url('assets/img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            color: var(--text-color);
            text-align: center;
        }
        
        .btn-booking {
            background-color: var(--primary-color);
            color: var(--text-color);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-booking:hover {
            background-color: var(--primary-hover);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline-light {
            color: var(--text-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-light:hover {
            background-color: var(--primary-color);
            color: var(--text-color);
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            width: 50%;
            height: 3px;
            background: var(--primary-color);
            bottom: -10px;
            left: 25%;
        }
    </style>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="spinner"></div>
    </div>

   

    <!-- Header -->
    <header class="header">
        
        <nav class="navbar navbar-expand-lg" style="background: white !important; background-color: white !important;">
            <div class="container-fluid px-3 px-md-4">
                <a class="navbar-brand p-0 m-0 d-flex align-items-center" href="index.php">
                    <img src="assets/images/logo.svg" alt="Nirali Makeup Studio" style="height: 65px; width: auto; margin-right: 15px;">
                    <span class="d-none d-md-inline-block align-self-center" style="font-size: 2rem; font-weight: 700; color: #333; font-family: 'Poppins', sans-serif; line-height: 1.2;">Nirali Makeup Studio</span>
                </a>
                
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#navbarNav" aria-controls="navbarNav" 
                        aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="collapse navbar-collapse p-3 p-lg-0" id="navbarNav" style="border-radius: 0 0 10px 10px; background: white !important; background-color: white !important; opacity: 1 !important; z-index: 9999 !important; position: relative;">
                    <button type="button" class="btn-close d-lg-none position-absolute" style="top: 10px; right: 15px; font-size: 1.5rem; z-index: 10000;" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <ul class="navbar-nav ms-auto me-auto" style="gap: 1rem;">
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('index.php'); ?>" href="index.php" style="font-size: 1.1rem; font-weight: 500;">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('about.php'); ?>" href="about.php" style="font-size: 1.1rem; font-weight: 500;">About</a>
                        </li>
                       
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('gallery.php'); ?>" href="gallery.php" style="font-size: 1.1rem; font-weight: 500;">Gallery</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('academy.php'); ?>" href="academy.php" style="font-size: 1.1rem; font-weight: 500;">
                                Academy
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('contact.php'); ?>" href="contact.php" style="font-size: 1.1rem; font-weight: 500;">Contact</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('blog.php'); ?>" href="blog.php" style="font-size: 1.1rem; font-weight: 500;">Blog</a>
                        </li>
                    </ul>
                    
                    <div class="d-flex align-items-center">
                        <a href="booking.php" class="btn btn-primary">BOOK NOW</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
