<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Include database configuration
require_once __DIR__ . '/../../includes/config.php';

// Get admin details
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Include admin authentication functions
require_once __DIR__ . '/admin_auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Inline Critical CSS -->
    <style>
        /* Critical CSS for initial render */
        
        /* Quick Links Styling */
        .icon-shape {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            transition: all 0.3s ease;
        }
        
        .bg-soft-primary {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }
        
        .bg-soft-success {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }
        
        .bg-soft-warning {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }
        
        .bg-soft-info {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }
        
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
        }
        
        .card {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        :root {
            --primary-bg: #FBF9F7;
            --primary-text: #2C2C2C;
            --sidebar-width: 250px;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--primary-bg);
            color: var(--primary-text);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        #sidebar-wrapper {
            min-width: var(--sidebar-width);
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            background: #fff;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        #page-content-wrapper {
            width: 100%;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
            background-color: var(--primary-bg);
        }
        .navbar {
            padding: 0.5rem 1rem;
        }
    </style>
    
    <!-- Load non-critical CSS asynchronously -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"></noscript>
    
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></noscript>
    
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <link rel="preload" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"></noscript>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css"></noscript>
    
    <style>
    :root {
        --primary: #4e73df;
        --secondary: #858796;
        --success: #1cc88a;
        --info: #36b9cc;
        --warning: #f6c23e;
        --danger: #e74a3b;
        --light: #f8f9fc;
        --dark: #5a5c69;
        --instagram: #e4405f;
    }
    
    .text-instagram {
        color: var(--instagram) !important;
    }
    
    .border-left-instagram {
        border-left: 0.25rem solid var(--instagram) !important;
    }
    :root {
        --primary-accent: #E7BFBF;
        --secondary-accent: #FFDAB9;
        --luxury-highlight: #D4AF37;
        --light-border: #E5E5E5;
        --hover-border: #E7BFBF;
        
{{ ... }}
        /* Layout */
        --sidebar-width: 250px;
        --top-navbar-height: 56px;
        --transition-speed: 0.3s;
        
        /* Shadows */
        --card-shadow: 0 2px 15px rgba(0,0,0,0.05);
        --hover-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--primary-bg);
        color: var(--primary-text);
        min-height: 100vh;
        overflow-x: hidden;
    }
    
    #wrapper {
        display: flex;
        width: 100%;
        min-height: 100vh;
    }
    
    /* Sidebar */
    #sidebar-wrapper {
        min-width: var(--sidebar-width);
        width: var(--sidebar-width);
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: var(--tertiary-bg);
        color: var(--primary-text);
        border-right: 1px solid var(--light-border);
        transition: all var(--transition-speed) ease;
        z-index: 1000;
        overflow-y: auto;
    }
    
    #page-content-wrapper {
        width: 100%;
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        transition: all var(--transition-speed) ease;
        background-color: var(--primary-bg);
    }
    
    /* Navbar */
    .navbar {
        padding: 0.75rem 1.5rem;
        background: var(--tertiary-bg) !important;
        border-bottom: 1px solid var(--light-border);
        position: sticky;
        top: 0;
        z-index: 900;
    }
    
    /* Cards */
    .card {
        background: var(--tertiary-bg);
        border: 1px solid var(--light-border);
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: var(--hover-shadow);
        border-color: var(--hover-border);
    }
    
    .card-header {
        background: var(--secondary-bg);
        border-bottom: 1px solid var(--light-border);
        color: var(--primary-text);
        font-weight: 600;
    }
    
    /* Buttons */
    .btn-primary {
        background-color: var(--primary-accent);
        border-color: var(--primary-accent);
        color: var(--primary-text);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover, .btn-primary:focus {
        background-color: #D9A7A7;
        border-color: #D9A7A7;
        color: white;
        transform: translateY(-1px);
    }
    
    .btn-outline-primary {
        color: var(--primary-text);
        border-color: var(--primary-accent);
        background: transparent;
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-accent);
        border-color: var(--primary-accent);
        color: var(--primary-text);
    }
    
    /* Forms */
    .form-control, .form-select {
        border: 1px solid var(--light-border);
        background-color: var(--tertiary-bg);
        color: var(--primary-text);
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-accent);
        box-shadow: 0 0 0 0.25rem rgba(231, 191, 191, 0.25);
    }
    
    /* Tables */
    .table {
        color: var(--primary-text);
    }
    
    .table th {
        background-color: var(--secondary-bg);
        border-bottom: 2px solid var(--light-border);
    }
    
    .table td {
        border-color: var(--light-border);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(231, 191, 191, 0.1);
    }
    
    /* Navigation */
    .list-group-item {
        background: transparent;
        border-color: var(--light-border);
        color: var(--primary-text);
    }
    
    .list-group-item:hover, .list-group-item:focus {
        background-color: rgba(231, 191, 191, 0.1);
        color: var(--primary-text);
    }
    
    .list-group-item.active {
        background-color: var(--primary-accent);
        border-color: var(--primary-accent);
        color: var(--primary-text);
    }
    
    /* Alerts */
    .alert {
        border: none;
        border-left: 4px solid transparent;
    }
    
    .alert-success {
        background-color: rgba(40, 167, 69, 0.1);
        border-left-color: #28a745;
        color: #155724;
    }
    
    .alert-danger {
        background-color: rgba(220, 53, 69, 0.1);
        border-left-color: #dc3545;
        color: #721c24;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        #wrapper {
            padding-left: 0;
        }
        
        #wrapper.toggled #sidebar-wrapper {
            margin-left: 0;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        #page-content-wrapper {
            margin-left: 0;
        }
        
        #wrapper.toggled #page-content-wrapper {
            margin-left: var(--sidebar-width);
        }
    }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading d-flex align-items-center justify-content-between p-4">
                <div class="d-flex align-items-center">
                    <span class="fw-bold" style="color: var(--primary-text);">Nirali Makeup Studio</span>
                </div>
                <button class="btn btn-link d-md-none" id="menu-toggle" style="color: var(--primary-text);">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="list-group list-group-flush px-3">
                <a href="index.php" class="list-group-item list-group-item-action rounded-3 mb-2 <?php echo isActive('index.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                
                <a href="services.php" class="list-group-item list-group-item-action rounded-3 mb-2 <?php echo (isActive('services.php') || isActive('service-edit.php')) ? 'active' : ''; ?>">
                    <i class="fas fa-spa me-2"></i> Services Management
                </a>
                
                <a href="gallery.php" class="list-group-item list-group-item-action rounded-3 mb-2 <?php echo isActive('gallery.php') ? 'active' : ''; ?>">
                    <i class="fas fa-images me-2"></i> Gallery
                </a>
                
                <a href="testimonials.php" class="list-group-item list-group-item-action rounded-3 mb-2 <?php echo isActive('testimonials.php') ? 'active' : ''; ?>">
                    <i class="fas fa-quote-left me-2"></i> Testimonials
                </a>
                
                <a href="manage_videos.php" class="list-group-item list-group-item-action rounded-3 mb-2 <?php echo isActive('videos.php') ? 'active' : ''; ?>">
                    <i class="fas fa-video me-2"></i> Manage Videos
                </a>
                
                <a href="blog.php" class="list-group-item list-group-item-action rounded-3 mb-2 <?php echo (isActive('blog.php') || isActive('blog-edit.php') || isActive('blog-categories.php')) ? 'active' : ''; ?>">
                    <i class="fas fa-blog me-2"></i> Blog Posts
                </a>
                
                <a href="courses.php" class="list-group-item list-group-item-action rounded-3 mb-2 <?php echo (isActive('courses.php') || isActive('course-edit.php')) ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap me-2"></i> Courses
                </a>
                
                <a href="bookings.php" class="list-group-item list-group-item-action rounded-3 mb-2 <?php echo isActive('bookings.php') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check me-2"></i> Bookings
                
                <a href="messages.php" class="list-group-item list-group-item-action rounded-3 mb-2 position-relative <?php echo isActive('messages.php') ? 'active' : ''; ?>">
                    <i class="fas fa-envelope me-2"></i> Messages
                    <?php
                    // Show unread message count if there are any
                    try {
                        $unread_count = 0;
                        $pdo = getDBConnection();
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $unread_count = (int)$result['count'];
                        
                        if ($unread_count > 0): ?>
                            <span class="position-absolute top-50 end-0 translate-middle-y badge rounded-pill bg-danger me-2">
                                <?php echo $unread_count; ?>
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        <?php endif;
                    } catch (Exception $e) {
                        // Silently handle any errors
                        error_log("Error fetching unread messages count: " . $e->getMessage());
                    }
                    ?>
                </a>
                
                <?php if ($admin_role === 'admin'): ?>
                <!-- Admin-only menu items can be added here in the future -->
                <?php endif; ?>
                
                <div class="mt-4 pt-3 border-top">
                    <!-- Social Media Links -->
                    <div class="px-3 mb-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3"></h6>
                        
                    </div>
                    
                    <a href="profile.php" class="list-group-item list-group-item-action rounded-3 mb-2 <?php echo isActive('profile.php') ? 'active' : ''; ?>">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                    
                    <a href="logout.php" class="list-group-item list-group-item-action rounded-3 text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
           
            
            <!-- Main Content -->
            <div class="container-fluid px-4 py-4">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <?php if (isset($breadcrumb)): ?>
                                <?php foreach ($breadcrumb as $title => $url): ?>
                                    <?php if ($url): ?>
                                        <li class="breadcrumb-item"><a href="<?php echo $url; ?>"><?php echo $title; ?></a></li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item active" aria-current="page"><?php echo $title; ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $page_title ?? 'Dashboard'; ?></li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </div>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
