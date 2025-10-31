-- Drop existing table if it exists
DROP TABLE IF EXISTS nirali_reels;

-- Create reels table with all necessary columns
CREATE TABLE IF NOT EXISTS nirali_reels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_path VARCHAR(500) NOT NULL COMMENT 'Path to the uploaded video file',
    thumbnail_path VARCHAR(500) DEFAULT NULL COMMENT 'Path to the video thumbnail',
    instagram_url VARCHAR(500) DEFAULT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
