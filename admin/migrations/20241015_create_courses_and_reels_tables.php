<?php
// Create courses and reels tables
$sql = [
    "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        short_description VARCHAR(500),
        duration VARCHAR(100) COMMENT 'e.g., 4 weeks, 2 months',
        price DECIMAL(10,2) DEFAULT 0.00,
        image VARCHAR(255) DEFAULT NULL,
        is_featured TINYINT(1) DEFAULT 0,
        display_order INT DEFAULT 0,
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

// Execute the SQL
foreach ($sql as $query) {
    if (!$conn->query($query)) {
        echo "Error creating tables: " . $conn->error;
        exit();
    }
}

echo "Successfully created courses and reels tables\n";
