<?php
require_once __DIR__ . '/../includes/config.php';

try {
    $pdo = getDBConnection();
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        // Create the table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                subject VARCHAR(255),
                message TEXT NOT NULL,
                status ENUM('new', 'in_progress', 'completed', 'spam') DEFAULT 'new',
                is_read TINYINT(1) DEFAULT 0,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_status (status),
                INDEX idx_email (email),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        echo "Contact messages table created successfully!<br>";
    } else {
        echo "Contact messages table already exists.<br>";
    }
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE contact_messages");
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
    
    echo "<a href='../contact.php'>Go to Contact Page</a> | ";
    echo "<a href='messages.php'>View Messages in Admin</a>";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
