<?php
require_once __DIR__ . '/../includes/config.php';

try {
    $pdo = getDBConnection();
    
    // Add is_read column if it doesn't exist
    $pdo->exec("
        ALTER TABLE contact_messages 
        ADD COLUMN IF NOT EXISTS is_read TINYINT(1) DEFAULT 0,
        MODIFY COLUMN status ENUM('new', 'in_progress', 'completed', 'spam') DEFAULT 'new'
    ");
    
    echo "Contact messages table updated successfully!<br>";
    echo "<a href='messages.php'>Go to Messages Page</a>";
    
} catch (PDOException $e) {
    die("Error updating contact_messages table: " . $e->getMessage());
}
