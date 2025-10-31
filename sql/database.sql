-- Create the database
CREATE DATABASE IF NOT EXISTS makeup_studio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE makeup_studio;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'editor',
    last_login DATETIME DEFAULT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(255),
    price DECIMAL(10,2) DEFAULT 0.00,
    duration INT DEFAULT 60 COMMENT 'Duration in minutes',
    image VARCHAR(255) DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gallery Categories
CREATE TABLE IF NOT EXISTS gallery_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    display_order INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gallery Images
CREATE TABLE IF NOT EXISTS gallery_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testimonials
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_image VARCHAR(255) DEFAULT NULL,
    position VARCHAR(100) DEFAULT NULL,
    content TEXT NOT NULL,
    rating TINYINT(1) DEFAULT 5,
    is_featured TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blog Categories
CREATE TABLE IF NOT EXISTS blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blog Posts
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT,
    excerpt TEXT,
    featured_image VARCHAR(255) DEFAULT NULL,
    meta_title VARCHAR(100),
    meta_description VARCHAR(160),
    meta_keywords VARCHAR(255),
    author_id INT,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    notes TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact Messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    status ENUM('new', 'in_progress', 'completed', 'spam') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Site Settings
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    is_public TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, email, password, full_name, role, status) 
VALUES ('admin', 'admin@makeupstudio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 1);

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_group, is_public) VALUES
('site_name', 'Makeup Studio', 'general', 1),
('site_email', 'info@makeupstudio.com', 'general', 1),
('site_phone', '+1 234 567 8900', 'contact', 1),
('site_address', '123 Beauty Street, City, State 12345', 'contact', 1),
('site_description', 'Professional Makeup Studio - Beauty services, makeup courses, and more', 'seo', 1),
('site_keywords', 'makeup, beauty, salon, bridal makeup, makeup courses, beauty treatments', 'seo', 1),
('facebook_url', '#', 'social', 1),
('instagram_url', '#', 'social', 1),
('twitter_url', '#', 'social', 1),
('pinterest_url', '#', 'social', 1),
('opening_hours', 'Mon-Sat: 9:00 AM - 8:00 PM', 'contact', 1);

-- Insert sample services
INSERT INTO services (title, slug, description, short_description, price, duration, is_featured, display_order, status) VALUES
('Bridal Makeup', 'bridal-makeup', 'Complete bridal makeup package for your special day', 'Look stunning on your wedding day', 150.00, 120, 1, 1, 1),
('Party Makeup', 'party-makeup', 'Glamorous makeup for parties and special events', 'Perfect look for any occasion', 80.00, 90, 1, 2, 1),
('Makeup Class - Basic', 'makeup-class-basic', 'Learn the basics of makeup application', 'Beginner-friendly makeup class', 100.00, 180, 1, 3, 1),
('Makeup Class - Advanced', 'makeup-class-advanced', 'Advanced makeup techniques for professionals', 'Take your skills to the next level', 200.00, 240, 1, 4, 1),
('Hair Styling', 'hair-styling', 'Professional hair styling for any occasion', 'Perfect hairstyle to match your look', 60.00, 60, 0, 5, 1);

-- Insert sample gallery categories
INSERT INTO gallery_categories (name, slug, description, display_order, status) VALUES
('Bridal', 'bridal', 'Bridal makeup and hairstyles', 1, 1),
('Party', 'party', 'Party and special events makeup', 2, 1),
('Editorial', 'editorial', 'Editorial and fashion makeup', 3, 1),
('Special Effects', 'special-effects', 'Special effects and creative makeup', 4, 1);

-- Insert sample testimonials
INSERT INTO testimonials (client_name, position, content, rating, is_featured, display_order, status) VALUES
('Sarah Johnson', 'Bride', 'Absolutely loved my bridal makeup! The team was professional and made me feel special on my big day. Highly recommended!', 5, 1, 1, 1),
('Emily Davis', 'Student', 'The makeup class was amazing! I learned so many new techniques that I can use every day. The instructor was very knowledgeable.', 5, 1, 2, 1),
('Jessica Wilson', 'Regular Client', 'I\'ve been coming here for years for all my special occasions. The team always makes me look and feel amazing!', 5, 1, 3, 1);
