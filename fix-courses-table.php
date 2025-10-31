<?php
require_once 'includes/config.php';

try {
    $pdo = getDBConnection();
    
    echo "<h1>Fixing courses table structure...</h1>";
    
    // Add missing columns if they don't exist
    $alterQueries = [
        "ALTER TABLE `courses` MODIFY COLUMN `description` TEXT NULL",
        "ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `short_description` VARCHAR(500) NULL AFTER `description`",
        "ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `display_order` INT DEFAULT 0 AFTER `is_featured`",
        "ALTER TABLE `courses` MODIFY COLUMN `is_featured` TINYINT(1) DEFAULT 0",
        "ALTER TABLE `courses` MODIFY COLUMN `status` TINYINT(1) DEFAULT 1"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            echo "<p style='color: green;'>✅ Successfully executed: " . htmlspecialchars($query) . "</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠️ Warning: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Show final table structure
    echo "<h2>Updated Table Structure:</h2>";
    $stmt = $pdo->query("DESCRIBE `courses`");
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
    
    echo "<p style='color: green; font-weight: bold;'>✅ Database structure has been updated successfully!</p>";
    echo "<p>You can now <a href='admin/courses.php'>go to the courses page</a>.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
