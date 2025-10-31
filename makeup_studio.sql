-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2025 at 07:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `makeup_studio`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','editor') DEFAULT 'editor',
  `last_login` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `last_login`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@makeupstudio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', '2025-10-30 12:58:57', 1, '2025-09-27 06:25:22', '2025-10-30 07:28:57');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Makeup Tips', 'makeup-tips', 'Helpful tips and tricks for makeup application', '2025-10-09 09:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `slug`, `content`, `excerpt`, `featured_image`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, '10 Essential Makeup Tips for Beginners', '10-essential-makeup-tips-for-beginners', '<h3>1. Start with Skincare</h3><p>Always begin with a clean, moisturized face. Good makeup starts with good skin care.</p><h3>2. Use Primer</h3><p>A good primer creates a smooth base for your foundation and helps your makeup last longer.</p>', 'Learn the top 10 essential makeup tips that every beginner should know to create flawless looks.', NULL, 'published', '2025-10-09 14:33:36', '2025-10-09 09:03:36', '2025-10-09 09:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `blog_post_categories`
--

CREATE TABLE `blog_post_categories` (
  `post_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_post_categories`
--

INSERT INTO `blog_post_categories` (`post_id`, `category_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `status` enum('new','in_progress','completed','spam') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `is_read`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Abhay Thummar', 'admin@gmail.com', '9106338157', 'ghd', 'asdfasdf', 1, 'new', '2025-10-05 13:15:33', '2025-10-05 13:15:39');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `duration` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `slug`, `description`, `short_description`, `duration`, `price`, `image`, `is_featured`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Professional Makeup Artistry', 'professional-makeup-artistry', 'Master the art of professional makeup with our comprehensive course covering all aspects of beauty makeup, including skin preparation, color theory, and application techniques for various skin types and face shapes.', 'Master the art of professional makeup with our comprehensive course covering all aspects of beauty makeup.', '3 Months', 25000.00, 'assets/images/1.JPG', 1, 1, 1, '2025-10-15 12:07:31', '2025-10-15 12:11:54'),
(2, 'Bridal Makeup Masterclass', 'bridal-makeup-masterclass', 'Specialize in bridal makeup techniques, from natural day looks to glamorous evening styles. Learn about bridal skin preparation, long-lasting makeup techniques, and how to handle different lighting conditions.', 'Specialize in bridal makeup techniques, from natural day looks to glamorous evening styles.', '2 Months', 35000.00, 'assets/images/2.JPG', 1, 2, 1, '2025-10-15 12:07:31', '2025-10-15 12:11:54'),
(3, 'Special Effects Makeup', 'special-effects-makeup', 'Learn the art of special effects makeup for theater, film, and television productions. This course covers aging, wounds, fantasy characters, and more advanced techniques used in the entertainment industry.', 'Learn the art of special effects makeup for theater, film, and television productions.', '6 Months', 45000.00, 'assets/images/3.JPG', 1, 3, 1, '2025-10-15 12:07:31', '2025-10-15 12:12:03');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_categories`
--

CREATE TABLE `gallery_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_categories`
--

INSERT INTO `gallery_categories` (`id`, `name`, `slug`, `description`, `display_order`, `status`, `created_at`) VALUES
(1, 'Bridal', 'bridal', 'Bridal makeup and hairstyles', 1, 1, '2025-09-27 06:25:22'),
(2, 'Party', 'party', 'Party and special events makeup', 2, 1, '2025-09-27 06:25:22'),
(3, 'Editorial', 'editorial', 'Editorial and fashion makeup', 3, 1, '2025-09-27 06:25:22'),
(4, 'Special Effects', 'special-effects', 'Special effects and creative makeup', 4, 1, '2025-09-27 06:25:22');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `category_id`, `title`, `description`, `image_path`, `display_order`, `is_featured`, `status`, `created_at`, `updated_at`) VALUES
(3, 2, 'bridel', '', 'uploads/gallery/2025/10/68df9bdf057be.jpg', 1, 1, 1, '2025-10-03 09:48:15', '2025-10-03 09:48:37'),
(4, 2, 'Bride of Nirali’s 👰‍♀️', '', 'uploads/gallery/2025/10/68e11e7f81897.jpg', 2, 1, 1, '2025-10-04 13:17:51', '2025-10-04 13:17:54'),
(5, 1, 'Bride of Nirali’s 👰‍♀️', '', 'uploads/gallery/2025/10/68e11f17e10be.jpg', 3, 1, 1, '2025-10-04 13:20:23', '2025-10-04 13:20:23');

-- --------------------------------------------------------

--
-- Table structure for table `instagram_reels`
--

CREATE TABLE `instagram_reels` (
  `id` int(11) NOT NULL,
  `video_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nirali_reels`
--

CREATE TABLE `nirali_reels` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `video_path` varchar(500) DEFAULT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `instagram_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT 60 COMMENT 'Duration in minutes',
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `slug`, `description`, `short_description`, `duration`, `price`, `image`, `is_featured`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(15, 'Hair Treatment', 'hair-treatment', 'Revitalize your hair with our professional hair treatments.', 'Professional Hair Treatment service at Nirali Makeup Studio', 60, 80.00, 'uploads/services/service_68f0d2a369882.png', 1, 2, 1, '2025-09-30 05:52:32', '2025-10-16 11:10:27'),
(73, 'Makeup Classes', 'makeup-classes', 'Learn professional makeup techniques from our certified makeup artists. Our courses cover everything from basic makeup application to advanced techniques.', 'Professional makeup training and classes', 180, 8999.00, 'uploads/services/service_68f0d287eac91.png', 1, 4, 1, '2025-10-15 12:29:07', '2025-10-16 11:09:59'),
(78, 'Professional Makeup', 'professional-makeup', 'Expert makeup services for all occasions including bridal, party, and special events.', 'Professional makeup is the skilled application of cosmetics, often including prosthetics and hairstyling, by a trained make-up artist (MUA) to create a specific look or character for various media.', 60, 100.00, 'uploads/services/service_68f0d2b389818.png', 1, 1, 1, '2025-10-16 09:19:33', '2025-10-16 11:18:18'),
(79, 'Skin Care', 'skin-care', 'Professional skincare treatments for a glowing complexion.', 'Professional makeup is the skilled application of cosmetics, often including prosthetics and hairstyling, by a trained make-up artist (MUA) to create a specific look or character for various media.', 60, 90.00, 'uploads/services/service_68f0d29403763.png', 1, 3, 1, '2025-10-16 09:19:33', '2025-10-16 11:18:51');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Bridal Makeup', 'bridal-makeup', 'Make your special day even more beautiful with our professional bridal makeup services.', NULL, 1, 1, '2025-09-30 05:00:19', '2025-09-30 05:00:19'),
(2, 'Party Makeup', 'party-makeup', 'Get ready to shine at any event with our professional party makeup services.', NULL, 1, 2, '2025-09-30 05:00:19', '2025-09-30 05:00:19'),
(3, 'Hair Styling', 'hair-styling', 'Complete your look with our professional hair styling services.', NULL, 1, 3, '2025-09-30 05:00:19', '2025-09-30 05:00:19'),
(4, 'Skincare', 'skincare', 'Nourish and rejuvenate your skin with our professional treatments.', NULL, 1, 4, '2025-09-30 05:00:19', '2025-09-30 05:00:19'),
(5, 'Makeup Classes', 'makeup-classes', 'Learn professional makeup techniques from our experts.', NULL, 1, 5, '2025-09-30 05:00:19', '2025-09-30 05:00:19'),
(6, 'Special Effects', 'special-effects', 'Transform into any character with our special effects makeup.', NULL, 1, 6, '2025-09-30 05:00:19', '2025-09-30 05:00:19'),
(7, 'Makeup', 'makeup', 'Makeup Services', NULL, 1, 1, '2025-09-30 05:52:32', '2025-09-30 05:52:32'),
(8, 'Hair', 'hair', 'Hair Services', NULL, 1, 2, '2025-09-30 05:52:32', '2025-09-30 05:52:32'),
(9, 'Skin Care', 'skin-care', 'Skin Care Services', NULL, 1, 3, '2025-09-30 05:52:32', '2025-09-30 05:52:32'),
(10, 'Nails', 'nails', 'Nails Services', NULL, 1, 4, '2025-09-30 05:52:32', '2025-09-30 05:52:32');

-- --------------------------------------------------------

--
-- Table structure for table `service_category_mapping`
--

CREATE TABLE `service_category_mapping` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_category_mapping`
--

INSERT INTO `service_category_mapping` (`id`, `service_id`, `category_id`, `created_at`) VALUES
(32, 80, 5, '2025-10-16 11:08:13'),
(35, 73, 6, '2025-10-16 11:09:59'),
(37, 15, 8, '2025-10-16 11:10:27'),
(39, 78, 7, '2025-10-16 11:18:18'),
(40, 79, 9, '2025-10-16 11:18:51');

-- --------------------------------------------------------

--
-- Table structure for table `service_images`
--

CREATE TABLE `service_images` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_images`
--

INSERT INTO `service_images` (`id`, `service_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES
(1, 1, 'uploads/services/gallery/gallery_68e0f82984a4f.jpg', NULL, 1, '2025-10-04 10:34:17'),
(2, 1, 'uploads/services/gallery/gallery_68e0f8298592b.jpg', NULL, 2, '2025-10-04 10:34:17');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Makeup Studio', 'general', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(2, 'site_email', 'info@makeupstudio.com', 'general', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(3, 'site_phone', '+1 234 567 8900', 'contact', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(4, 'site_address', '123 Beauty Street, City, State 12345', 'contact', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(5, 'site_description', 'Professional Makeup Studio - Beauty services, makeup courses, and more', 'seo', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(6, 'site_keywords', 'makeup, beauty, salon, bridal makeup, makeup courses, beauty treatments', 'seo', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(7, 'facebook_url', '#', 'social', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(8, 'instagram_url', '#', 'social', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(9, 'twitter_url', '#', 'social', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(10, 'pinterest_url', '#', 'social', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22'),
(11, 'opening_hours', 'Mon-Sat: 9:00 AM - 8:00 PM', 'contact', 1, '2025-09-27 06:25:22', '2025-09-27 06:25:22');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_image` varchar(255) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` tinyint(1) DEFAULT 5,
  `is_featured` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `client_image`, `position`, `content`, `rating`, `is_featured`, `display_order`, `status`, `created_at`) VALUES
(1, 'Sarah Johnson', 'uploads/testimonials/testimonial_68df9d7f73454.jpg', 'Bride', 'Absolutely loved my bridal makeup! The team was professional and made me feel special on my big day. Highly recommended!', 5, 1, 1, 1, '2025-09-27 06:25:22'),
(2, 'Emily Davis', NULL, 'Student', 'The makeup class was amazing! I learned so many new techniques that I can use every day. The instructor was very knowledgeable.', 5, 1, 2, 1, '2025-09-27 06:25:22'),
(3, 'Jessica Wilson', NULL, 'Regular Client', 'I\'ve been coming here for years for all my special occasions. The team always makes me look and feel amazing!', 5, 1, 3, 1, '2025-09-27 06:25:22');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(255) NOT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `display_location` enum('home','academy','both') NOT NULL DEFAULT 'home',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `title`, `description`, `video_url`, `thumbnail_url`, `display_order`, `is_active`, `display_location`, `created_at`, `updated_at`) VALUES
(3, 'Bride of Nirali’s 👰‍♀️', '', 'uploads/videos/video_1759583682.mp4', '', 1, 1, 'home', '2025-10-04 13:14:42', '2025-10-04 13:15:31'),
(4, 'Redefining modern bridal beauty✨', '', 'uploads/videos/video_1759583701.mp4', '', 0, 1, 'home', '2025-10-04 13:15:01', '2025-10-04 13:15:01'),
(5, 'Bride of Nirali’s 👰‍♀️', '', 'uploads/videos/video_1759583723.mp4', '', 2, 1, 'home', '2025-10-04 13:15:23', '2025-10-04 13:15:23'),
(6, 'Bride of Nirali’s 👰‍♀️', '', 'uploads/videos/video_1760616420.mp4', '', 3, 1, 'home', '2025-10-13 05:48:18', '2025-10-16 12:07:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `blog_post_categories`
--
ALTER TABLE `blog_post_categories`
  ADD PRIMARY KEY (`post_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `gallery_categories`
--
ALTER TABLE `gallery_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `instagram_reels`
--
ALTER TABLE `instagram_reels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nirali_reels`
--
ALTER TABLE `nirali_reels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `service_category_mapping`
--
ALTER TABLE `service_category_mapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_category` (`service_id`,`category_id`);

--
-- Indexes for table `service_images`
--
ALTER TABLE `service_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gallery_categories`
--
ALTER TABLE `gallery_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `instagram_reels`
--
ALTER TABLE `instagram_reels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nirali_reels`
--
ALTER TABLE `nirali_reels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `service_category_mapping`
--
ALTER TABLE `service_category_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `service_images`
--
ALTER TABLE `service_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blog_post_categories`
--
ALTER TABLE `blog_post_categories`
  ADD CONSTRAINT `blog_post_categories_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_post_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `gallery_categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
