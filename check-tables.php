<?php
require_once 'includes/db.php';

try {
    echo "<h2>Checking Database Tables</h2>";
    
    // Check if service_categories table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'service_categories'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ service_categories table exists</p>";
        
        // Count categories
        $count = $pdo->query("SELECT COUNT(*) as count FROM service_categories")->fetch()['count'];
        echo "<p>Found $count categories in service_categories table</p>";
    } else {
        echo "<p style='color: red;'>✗ service_categories table is missing</p>";
    }
    
    // Check if services table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'services'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ services table exists</p>";
        
        // Count services
        $count = $pdo->query("SELECT COUNT(*) as count FROM services")->fetch()['count'];
        echo "<p>Found $count services in services table</p>";
    } else {
        echo "<p style='color: red;'>✗ services table is missing</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Show create table statements if tables are missing
echo "<h3>If tables are missing, run these SQL commands in phpMyAdmin:</h3>";
echo "<pre>";
?>
-- Create service_categories table if not exists
CREATE TABLE IF NOT EXISTS `service_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create services table if not exists
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `image` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `features` text DEFAULT NULL COMMENT 'One feature per line',
  `additional_info` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample categories if none exist
INSERT IGNORE INTO `service_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Bridal Makeup', 'bridal-makeup', 'Complete bridal makeup packages for your special day'),
(2, 'Party Makeup', 'party-makeup', 'Glamorous makeup for parties and special occasions'),
(3, 'Skin Care', 'skin-care', 'Professional skin care treatments'),
(4, 'Hair Styling', 'hair-styling', 'Hair styling and treatments');

-- Insert sample services if none exist
INSERT IGNORE INTO `services` (`id`, `category_id`, `title`, `slug`, `description`, `price`, `duration`, `features`) VALUES
(1, 1, 'Complete Bridal Makeup', 'complete-bridal-makeup', 'Full bridal makeup package including trial, pre-bridal care, and wedding day makeup.', 15000.00, 180, 'Airbrush makeup\nWaterproof and long-lasting\nTrial session included\nPre-bridal consultation'),
(2, 2, 'Evening Party Makeup', 'evening-party-makeup', 'Glamorous makeup perfect for evening parties and special events.', 5000.00, 90, 'HD makeup\nFalse lashes included\nTouch-up kit provided\nWaterproof and smudge-proof'),
(3, 3, 'Facial Treatment', 'facial-treatment', 'Revitalizing facial treatment for glowing skin.', 2500.00, 60, 'Deep cleansing\nExfoliation\nMoisturizing\nSuitable for all skin types'),
(4, 4, 'Bridal Hairstyling', 'bridal-hairstyling', 'Elegant hairstyling to complement your bridal look.', 7000.00, 120, 'Hair extensions available\nHair accessories included\nTrial session\nLong-lasting hold');
<?php
echo "</pre>";

echo "<p><a href='service-details.php?slug=complete-bridal-makeup'>Try service details page again</a></p>";
?>
