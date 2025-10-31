<?php
// Create reels table
$sql = "CREATE TABLE IF NOT EXISTS `nirali_reels` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text,
    `instagram_url` varchar(255) DEFAULT NULL,
    `video_path` varchar(255) DEFAULT NULL,
    `thumbnail_path` varchar(255) DEFAULT NULL,
    `display_order` int(11) DEFAULT 0,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Execute the SQL
if ($conn->query($sql) === TRUE) {
    echo "Reels table created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
?>
