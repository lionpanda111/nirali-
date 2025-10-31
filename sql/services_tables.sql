-- Create service categories table
CREATE TABLE IF NOT EXISTS `service_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create services table
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `duration` int(11) DEFAULT 60 COMMENT 'Duration in minutes',
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create service category mapping table
CREATE TABLE IF NOT EXISTS `service_category_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_category` (`service_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `service_category_mapping_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_category_mapping_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample categories
INSERT INTO `service_categories` (`name`, `slug`, `description`, `display_order`) VALUES
('Bridal Makeup', 'bridal-makeup', 'Complete bridal makeup packages for your special day', 1),
('Party Makeup', 'party-makeup', 'Glamorous makeup for all your special occasions', 2),
('Hair Styling', 'hair-styling', 'Professional hair styling for any event', 3),
('Skin Care', 'skin-care', 'Luxurious facial treatments for healthy, glowing skin', 4),
('Traditional Look', 'traditional-look', 'Authentic traditional makeup and styling', 5);

-- Insert sample services
INSERT INTO `services` (`title`, `slug`, `description`, `short_description`, `price`, `duration`, `is_featured`, `display_order`) VALUES
('Complete Bridal Package', 'complete-bridal-package', 'Full bridal makeup with trial session, touch-up kit, and on-location service', 'Everything you need for your perfect wedding day look', 300.00, 180, 1, 1),
('Evening Glam Makeup', 'evening-glam-makeup', 'Stunning makeup for evening events and parties with long-lasting finish', 'Perfect for your special night out', 100.00, 90, 1, 2),
('Updo Hairstyle', 'updo-hairstyle', 'Elegant updo hairstyle for any special occasion', 'Classic and sophisticated updo', 75.00, 60, 1, 3),
('Hydrating Facial', 'hydrating-facial', 'Deeply hydrating facial treatment for all skin types', 'Restore moisture and glow to your skin', 80.00, 60, 1, 4),
('Traditional Bridal Look', 'traditional-bridal-look', 'Complete traditional bridal makeup and styling', 'Authentic and elegant traditional look', 250.00, 150, 1, 5);

-- Map services to categories
INSERT INTO `service_category_mapping` (`service_id`, `category_id`) VALUES
(1, 1), (2, 2), (3, 3), (4, 4), (5, 5);
