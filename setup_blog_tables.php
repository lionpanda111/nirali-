<?php
/**
 * Setup Blog Tables
 * 
 * This script will create the necessary database tables for the blog.
 * Run this script once to set up the blog functionality.
 */

// Include the main config file
require_once __DIR__ . '/includes/config.php';

// Check if user is logged in as admin
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('Please log in as administrator to run this script.');
}

// Check if the form was submitted
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tables'])) {
    try {
        $db = getDBConnection();
        
        // Create blog_posts table
        $sql = "CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content LONGTEXT NOT NULL,
            excerpt TEXT,
            featured_image VARCHAR(255),
            status ENUM('draft', 'published') DEFAULT 'draft',
            published_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_slug (slug),
            KEY idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($sql);
        $message .= "✅ Blog posts table created successfully.<br>";
        
        // Create blog_categories table
        $sql = "CREATE TABLE IF NOT EXISTS blog_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($sql);
        $message .= "✅ Blog categories table created successfully.<br>";
        
        // Create blog_post_categories table
        $sql = "CREATE TABLE IF NOT EXISTS blog_post_categories (
            post_id INT NOT NULL,
            category_id INT NOT NULL,
            PRIMARY KEY (post_id, category_id),
            FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($sql);
        $message .= "✅ Blog post categories table created successfully.<br>";
        
        // Add some sample data
        try {
            // Add a sample category
            $stmt = $db->prepare("INSERT IGNORE INTO blog_categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->execute([
                'Makeup Tips', 
                'makeup-tips', 
                'Helpful tips and tricks for makeup application'
            ]);
            
            // Add a sample blog post
            $stmt = $db->prepare("
                INSERT INTO blog_posts 
                (title, slug, content, excerpt, status, published_at) 
                VALUES (?, ?, ?, ?, 'published', NOW())
            ");
            
            $title = '10 Essential Makeup Tips for Beginners';
            $slug = '10-essential-makeup-tips-for-beginners';
            $content = '<p>Starting with makeup can be overwhelming, but with these essential tips, you\'ll be a pro in no time!</p>'
                     . '<h3>1. Start with Skincare</h3>'
                     . '<p>Always begin with a clean, moisturized face. Good makeup starts with good skin care.</p>'
                     . '<h3>2. Use Primer</h3>'
                     . '<p>A good primer creates a smooth base for your foundation and helps your makeup last longer.</p>'
                     . '<h3>3. Find Your Perfect Foundation Match</h3>'
                     . '<p>Test foundation on your jawline in natural light to find your perfect match.</p>';
            $excerpt = 'Learn the top 10 essential makeup tips that every beginner should know to create flawless looks.';
            
            $stmt->execute([$title, $slug, $content, $excerpt]);
            $post_id = $db->lastInsertId();
            
            // Link post to category
            $stmt = $db->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, 1)");
            $stmt->execute([$post_id]);
            
            $message .= "✅ Sample blog post and category added successfully.<br>";
            
        } catch (PDOException $e) {
            // Ignore errors for sample data insertion
            $message .= "ℹ️ Sample data already exists or couldn't be added: " . $e->getMessage() . "<br>";
        }
        
    } catch (PDOException $e) {
        $error = "❌ Error creating blog tables: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Blog Tables - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 2rem;
            background-color: #f8f9fa;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        .btn-setup {
            padding: 0.75rem 1.5rem;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <h1 class="mb-4">Setup Blog Tables</h1>
            <p class="lead">This script will create the necessary database tables for the blog functionality.</p>
            
            <?php if ($message): ?>
                <div class="success-msg">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-msg">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tables to be created:</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>blog_posts</strong> - Stores all blog posts</li>
                        <li><strong>blog_categories</strong> - Stores blog post categories</li>
                        <li><strong>blog_post_categories</strong> - Maps posts to categories (many-to-many relationship)</li>
                    </ul>
                </div>
            </div>
            
            <form method="post" action="">
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="confirm" required>
                    <label class="form-check-label" for="confirm">
                        I have backed up my database. I understand that this action cannot be undone.
                    </label>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="admin/" class="btn btn-outline-secondary me-md-2">Back to Admin</a>
                    <button type="submit" name="create_tables" class="btn btn-primary btn-setup">
                        <i class="fas fa-database me-2"></i> Create Blog Tables
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
